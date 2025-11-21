<?php

namespace Fleetbase\RegistryBridge\Support;

use Fleetbase\Models\User;
use Fleetbase\RegistryBridge\Models\RegistryUser;
use Fleetbase\Support\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Bridge
{
    /**
     * Creates a complete URL by concatenating the base URL from the configuration with the provided URI.
     *
     * This method ensures that the base URL from the configuration has a trailing slash before
     * concatenating it with the provided URI. It handles URL formation for making HTTP requests.
     *
     * @param string $uri the specific URI to append to the base URL
     *
     * @return string the complete URL formed by concatenating the base URL and the provided URI
     */
    private static function createUrl(string $uri = ''): string
    {
        $url = config('registry-bridge.registry.host');

        return rtrim($url, '/') . '/' . $uri;
    }

    /**
     * Sends a GET request to the specified URI.
     *
     * @param string $uri        URI to send the GET request to
     * @param array  $parameters parameters to include in the request
     * @param array  $options    additional options for the HTTP client
     *
     * @return \Illuminate\Http\Client\Response
     */
    public static function get($uri, $parameters = [], $options = [])
    {
        return Http::withOptions($options)->get(static::createUrl($uri), $parameters);
    }

    /**
     * Sends a POST request to the specified URI.
     *
     * @param string $uri        URI to send the GET request to
     * @param array  $parameters parameters to include in the request
     * @param array  $options    additional options for the HTTP client
     *
     * @return \Illuminate\Http\Client\Response
     */
    public static function post($uri, $parameters = [], $options = [])
    {
        return Http::withOptions($options)->post(static::createUrl($uri), $parameters);
    }

    /**
     * Logs in to the npm registry using the provided user and retrieves the authentication token.
     *
     * This method uses the npm-cli-login tool to authenticate with the npm registry using the
     * provided user's credentials, retrieves the authentication token from the .npmrc file,
     * and associates it with the currently authenticated user in the application. The registry
     * token is stored in the database for the user's current session.
     *
     * @param User   $user     the fleetbase user model containing the username and email
     * @param string $password the npm password for the user
     *
     * @return RegistryUser the RegistryUser model containing the registry token and associated data
     *
     * @throws \Exception If there is no active session, the .npmrc file is not found, the auth token is not found, or the npm login fails.
     */
    public static function loginWithUser(User $user, string $password): RegistryUser
    {
        return static::login($user->username, $password, $user->email);
    }

    /**
     * Logs in to the fleetbase registry and retrieves the authentication token.
     *
     * This method uses the npm-cli-login tool to authenticate with the fleetbase registry,
     * retrieves the authentication token from the .npmrc file, and associates it with
     * the currently authenticated user in the application. The registry token is stored
     * in the database for the user's current session.
     *
     * @param string $username the npm username
     * @param string $password the npm password
     * @param string $email    the npm email
     *
     * @return RegistryUser the RegistryUser model containing the registry token and associated data
     *
     * @throws \Exception If there is no active session, the .npmrc file is not found, the auth token is not found, or the npm login fails.
     */
    public static function login(string $username, string $password, string $email): RegistryUser
    {
        // Session is required
        if (session()->missing(['company', 'user'])) {
            throw new \Exception('No active session to create registry token for.');
        }

        // Get registry
        $registry = static::createUrl();

        // Set .npmrc path
        $npmrcPath     = 'tmp/.npmrc-' . session('user');

        // Create .npmrc file
        Storage::disk('local')->put($npmrcPath, '');

        // Prepare command
        $process  = new Process([
            'npm-cli-login',
            '-u', $username,
            '-p', $password,
            '-e', $email,
            '-r', $registry,
            '-s', 'false',
            '--config-path', storage_path('app/' . $npmrcPath),
        ]);

        // Set timeout
        $process->setTimeout(60);

        try {
            // Run the process
            $process->mustRun();

            // Check if .npmrc file exists
            if (!Storage::drive('local')->exists($npmrcPath)) {
                throw new \Exception('.npmrc file not found');
            }

            // Remove protocol from registry URL for matching
            $registryHost = preg_replace('/^https?:\/\//', '', $registry);

            // Read the .npmrc file to get the auth token
            $npmrcContent = Storage::drive('local')->get($npmrcPath);
            $lines        = explode("\n", $npmrcContent);
            $authToken    = null;

            foreach ($lines as $line) {
                $line = trim($line);
                if (Str::contains($line, $registryHost) && Str::contains($line, '_authToken=')) {
                    $parts = explode('_authToken=', $line);
                    if (count($parts) === 2) {
                        $authToken = trim($parts[1], ' "');
                        break;
                    }
                }
            }

            // Delete .npmrc file
            Storage::drive('local')->delete($npmrcPath);

            if ($authToken) {
                // Get current authenticated user
                $user = Auth::getUserFromSession();

                // Create or update registry user for current session
                $registryUser = RegistryUser::updateOrCreate(
                    ['company_uuid' => session('company'), 'user_uuid' => $user->uuid],
                    ['registry_token' => $authToken, 'scope' => '*', 'expires_at' => now()->addYear(), 'name' => $user->public_id . ' developer token']
                );

                return $registryUser;
            }

            throw new \Exception('Auth token not found in .npmrc');
        } catch (ProcessFailedException $exception) {
            throw new \Exception('npm login failed: ' . $exception->getMessage());
        }
    }
}
