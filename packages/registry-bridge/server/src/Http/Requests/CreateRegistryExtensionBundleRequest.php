<?php

namespace Fleetbase\RegistryBridge\Http\Requests;

use Fleetbase\Http\Requests\FleetbaseRequest;
use Fleetbase\Models\File;
use Fleetbase\RegistryBridge\Models\RegistryExtension;
use Illuminate\Support\Facades\Validator;

class CreateRegistryExtensionBundleRequest extends FleetbaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return session('company');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Validator::extend('valid_extension', function ($attribute, $value, $parameters, $validator) {
            return RegistryExtension::where(['uuid' => $value, 'company_uuid' => session('company')])->whereNull('deleted_at')->exists();
        }, 'Invalid extension provided for bundle submission.');

        Validator::extend('valid_bundle', function ($attribute, $value, $parameters, $validator) {
            return File::where(['uuid' => $value, 'company_uuid' => session('company'), 'subject_uuid' => $this->input('registryExtensionBundle.extension_uuid'), 'type' => 'extension_bundle'])->whereNull('deleted_at')->exists();
        }, 'Invalid bundle zip uploaded for submission.');

        return [
            'extension_uuid'        => ['required', 'valid_extension'],
            'bundle_uuid'           => ['required', 'valid_bundle'],
        ];
    }
}
