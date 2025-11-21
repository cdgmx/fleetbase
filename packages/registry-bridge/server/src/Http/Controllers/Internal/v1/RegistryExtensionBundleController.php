<?php

namespace Fleetbase\RegistryBridge\Http\Controllers\Internal\v1;

use Fleetbase\Exceptions\FleetbaseRequestValidationException;
use Fleetbase\Models\File;
use Fleetbase\RegistryBridge\Http\Controllers\RegistryBridgeController;
use Fleetbase\RegistryBridge\Http\Requests\CreateRegistryExtensionBundleRequest;
use Fleetbase\RegistryBridge\Http\Requests\RegistryExtensionActionRequest;
use Fleetbase\RegistryBridge\Models\RegistryExtensionBundle;
use Fleetbase\RegistryBridge\Support\Utils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class RegistryExtensionBundleController extends RegistryBridgeController
{
    /**
     * The resource to query.
     *
     * @var string
     */
    public $resource = 'registry_extension_bundle';

    /**
     * Creates a record with request payload.
     *
     * @return \Illuminate\Http\Response
     */
    public function createRecord(Request $request)
    {
        $extensionId = $request->input('subject_uuid');

        // Create validation request
        $createRegistryExtensionBundleRequest  = CreateRegistryExtensionBundleRequest::createFrom($request);
        $rules                                 = $createRegistryExtensionBundleRequest->rules();

        // Manually validate request
        $validator = Validator::make($request->input('registryExtensionBundle'), $rules);
        if ($validator->fails()) {
            return $createRegistryExtensionBundleRequest->responseWithErrors($validator);
        }

        // Extract bundle extension json for file validation
        $bundleFile = File::where('uuid', $request->input('registryExtensionBundle.bundle_uuid'))->first();
        if (!$bundleFile) {
            return response()->error('Unable to find bundle file for validation.');
        }

        // Get extension.json contents
        $bundleData    = RegistryExtensionBundle::extractBundleData($bundleFile);
        $extensionJson = Utils::getObjectKeyValue($bundleData, 'extension.json');
        if (!$extensionJson) {
            return response()->error('Unable to find `extension.json` file required in bundle.');
        }

        // Check if version is set
        if (!isset($extensionJson->version)) {
            return response()->error('No `version` set in the `extension.json`');
        }

        // Check if either api or engine property is set
        if (!isset($extensionJson->engine) && !isset($extensionJson->api)) {
            return response()->error('No `api` or `engine` property set in the `extension.json`');
        }

        // Set bundle number
        $numberOfBundles = RegistryExtensionBundle::whereHas('extension', function ($query) use ($extensionId) {
            $query->where('public_id', $extensionId);
        })->count();
        $extensionJson->bundle_number = ($numberOfBundles ?? 0) + 1;

        try {
            $record = $this->model->createRecordFromRequest($request);

            // Update the record version from extension json
            $record->update(['bundle_number' => $extensionJson->bundle_number, 'version' => $extensionJson->version]);
            $record->updateMetaProperties((array) $bundleData);

            return ['registryExtensionBundle' => new $this->resource($record)];
        } catch (\Throwable $e) {
            return response()->error($e->getMessage());
        } catch (\Illuminate\Database\QueryException $e) {
            return response()->error($e->getMessage());
        } catch (FleetbaseRequestValidationException $e) {
            return response()->error($e->getErrors());
        }
    }

    /**
     * Handles the download request for an extension bundle.
     *
     * This function retrieves a specific `RegistryExtension` by its ID and attempts to download
     * its latest bundle. If the extension exists and has an associated bundle, it returns a download response
     * with the appropriate file. If the extension doesn't exist or doesn't have a bundle, it returns an error response.
     *
     * @param RegistryExtensionActionRequest $request the validated request object
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\Response
     *                                                                                        Returns a download response for the bundle if successful, or an error response if not
     */
    public function download(RegistryExtensionActionRequest $request)
    {
        $id              = $request->input('id');
        $extensionBundle = RegistryExtensionBundle::find($id);
        if ($extensionBundle && $extensionBundle->bundle) {
            return Storage::disk($extensionBundle->bundle->disk)->download($extensionBundle->bundle->path, $extensionBundle->bundle->name);
        }

        return response()->error('Failed to download extension bundle');
    }
}
