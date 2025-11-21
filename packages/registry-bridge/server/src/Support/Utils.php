<?php

namespace Fleetbase\RegistryBridge\Support;

use Fleetbase\Support\Utils as SupportUtils;
use Stripe\StripeClient;

class Utils extends SupportUtils
{
    /**
     * Get the StripeClient instance.
     */
    public static function getStripeClient(array $options = []): ?StripeClient
    {
        return new StripeClient([
            'api_key' => config('registry-bridge.stripe.secret'),
            ...$options,
        ]);
    }

    /**
     * Set the npm configuration for the console.
     *
     * This function sets the npm configuration in the .npmrc file located in the console path.
     * It sets the registry for npm and the Fleetbase scope registry.
     * If the .npmrc file does not exist or if the reset flag is set to true, it writes the configuration to the file.
     *
     * @param bool $reset whether to reset the configuration if the file already exists
     *
     * @return bool true if the configuration was set, false otherwise
     */
    public static function setConsoleNpmrcConfig(bool $reset = false): bool
    {
        $npmrcPath             = static::consolePath('.npmrc');
        $registryHost          = config('registry-bridge.registry.host', env('REGISTRY_HOST', 'https://registry.fleetbase.io'));
        $config                = implode(PHP_EOL, [
            'registry=https://registry.npmjs.org/',
            '@fleetbase:registry=' . rtrim($registryHost, '/') . '/',
        ]) . PHP_EOL;

        if (!file_exists($npmrcPath) || $reset === true) {
            file_put_contents($npmrcPath, $config, LOCK_EX);

            return true;
        }

        return false;
    }

    /**
     * Set the global npm authentication token.
     *
     * This function sets the authentication token in the global .npmrc file located in the user's home directory.
     * If the .npmrc file does not exist or if the reset flag is set to true, it writes the authentication token to the file.
     *
     * @param bool $reset whether to reset the configuration if the file already exists
     *
     * @return bool true if the authentication token was set, false otherwise
     */
    public static function setGlobalNpmrcAuthKey(bool $reset = false): bool
    {
        $homePath               = rtrim(getenv('HOME'), DIRECTORY_SEPARATOR);
        $npmrcPath              = $homePath . DIRECTORY_SEPARATOR . '.npmrc';
        $registryHost           = config('registry-bridge.registry.host', env('REGISTRY_HOST', 'https://registry.fleetbase.io'));
        $registryToken          = config('registry-bridge.registry.token', env('REGISTRY_TOKEN'));
        $authString             = '//' . str_replace(['http://', 'https://'], '', rtrim($registryHost, '/')) . '/:_authToken="' . $registryToken . '"' . PHP_EOL;

        if (!file_exists($npmrcPath) || $reset === true) {
            file_put_contents($npmrcPath, $authString, LOCK_EX);

            return true;
        }

        return false;
    }

    /**
     * Set the Composer authentication configuration.
     *
     * This function sets or updates the auth.json file with the registry token for authentication.
     * It ensures that no bearer tokens with null or empty values are set.
     *
     * @return bool true if the operation was successful, otherwise false
     *
     * @throws \RuntimeException if there is a JSON encoding/decoding error
     */
    public static function setComposerAuthConfig(): bool
    {
        $composerAuthPath = base_path('auth.json');
        $registryHost     = static::getDomainFromUrl(config('registry-bridge.registry.host', env('REGISTRY_HOST', 'https://registry.fleetbase.io')), true);
        $registryToken    = config('registry-bridge.registry.token', env('REGISTRY_TOKEN'));

        // Ensure the registry token is not null or empty
        if (empty($registryToken)) {
            return false;
        }

        $newBearerConfig = [
            'bearer' => [
                $registryHost => $registryToken,
            ],
        ];

        $currentConfig = [];

        if (file_exists($composerAuthPath)) {
            $jsonContent   = file_get_contents($composerAuthPath);
            $currentConfig = json_decode($jsonContent, true);
            if ($currentConfig === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
            }

            // Merge existing config with the new bearer config
            if (isset($currentConfig['bearer'])) {
                $currentConfig['bearer'] = array_merge($currentConfig['bearer'], $newBearerConfig['bearer']);
            } else {
                $currentConfig['bearer'] = $newBearerConfig['bearer'];
            }
        } else {
            $currentConfig = $newBearerConfig;
        }

        $jsonContent = json_encode($currentConfig, JSON_PRETTY_PRINT);
        if ($jsonContent === false) {
            throw new \RuntimeException('Failed to encode JSON: ' . json_last_error_msg());
        }

        file_put_contents($composerAuthPath, $jsonContent, LOCK_EX);

        return true;
    }

    /**
     * Initializes and sets up the npm registry authentication configuration.
     *
     * This method constructs the registry authentication string from configuration settings,
     * checks for the existence of an npmrc file in the user's home directory, and creates it
     * with the registry authentication string if it doesn't already exist.
     *
     * The registry configuration and token are pulled from the application's configuration files.
     * It ensures the path to the .npmrc file is correctly formed regardless of trailing slashes
     * in the HOME directory path or the registry host configuration.
     *
     * @param bool $reset - Overwrites existing file, "resetting" the .npmrc
     *
     * @return void
     */
    public static function bootRegistryAuth(bool $reset = false)
    {
        Utils::setConsoleNpmrcConfig($reset);
        Utils::setGlobalNpmrcAuthKey($reset);
        Utils::setComposerAuthConfig();
    }
}
