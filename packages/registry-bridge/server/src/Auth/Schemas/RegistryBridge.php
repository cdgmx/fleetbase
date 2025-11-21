<?php

namespace Fleetbase\RegistryBridge\Auth\Schemas;

class RegistryBridge
{
    /**
     * The permission schema Name.
     */
    public string $name = 'registry-bridge';

    /**
     * The permission schema Polict Name.
     */
    public string $policyName = 'RegistryBridge';

    /**
     * Guards these permissions should apply to.
     */
    public array $guards = ['sanctum'];

    /**
     * The permission schema resources.
     */
    public array $resources = [
        [
            'name'    => 'registry-token',
            'actions' => ['export'],
        ],
        [
            'name'    => 'extension',
            'actions' => ['uninstall', 'install', 'purchase'],
        ],
        [
            'name'           => 'extension-bundle',
            'actions'        => ['submit'],
            'remove_actions' => [],
        ],
        [
            'name'           => 'extension-purchase',
            'actions'        => [],
            'remove_actions' => ['create', 'update', 'delete'],
        ],
        [
            'name'           => 'extension-install',
            'actions'        => [],
            'remove_actions' => ['create', 'update', 'delete'],
        ],
        [
            'name'           => 'extension-analytic',
            'actions'        => [],
            'remove_actions' => ['create', 'update', 'delete'],
        ],
        [
            'name'           => 'extension-payment',
            'actions'        => ['onboard'],
            'remove_actions' => ['create', 'update', 'delete'],
        ],
    ];

    /**
     * Policies provided by this schema.
     */
    public array $policies = [
        [
            'name'        => 'ExtensionDeveloper',
            'description' => 'Policy for developing and publishing extensions.',
            'permissions' => [
                'see extension',
                'see registry-token',
                'list registry-token',
                'create registry-token',
                'see extension-bundle',
                'list extension-bundle',
                'view extension-bundle',
                'create extension-bundle',
                'update extension-bundle',
                'delete extension-bundle',
                'submit extension-bundle',
                'see extension-analytic',
                'view extension-analytic',
                'see extension-payment',
                'view extension-payment',
                'list extension',
            ],
        ],
        [
            'name'        => 'ExtensionMarketing',
            'description' => 'Policy to provide insight for marketing extensions.',
            'permissions' => [
                'see extension',
                'see extension-bundle',
                'list extension-bundle',
                'update extension-bundle',
                'see extension-analytic',
                'view extension-analytic',
                'list extension-analytic',
                'list extension',
            ],
        ],
        [
            'name'        => 'ExtensionFinance',
            'description' => 'Policy for managing finance and accounting for extensions.',
            'permissions' => [
                'see extension',
                'see extension-analytic',
                'view extension-analytic',
                'list extension-analytic',
                'see extension-payment',
                'view extension-payment',
                'list extension-payment',
                'onboard extension-payment',
                'list extension',
            ],
        ],
        [
            'name'        => 'ExtensionSales',
            'description' => 'Policy for managing sales reports of extensions.',
            'permissions' => [
                'see extension',
                'see extension-analytic',
                'view extension-analytic',
                'list extension-analytic',
                'see extension-payment',
                'view extension-payment',
                'list extension-payment',
                'see extension-purchase',
                'view extension-purchase',
                'list extension-purchase',
                'list extension',
            ],
        ],
        [
            'name'        => 'ExtensionManager',
            'description' => 'Policy for complete management of extensions.',
            'permissions' => [
                'see extension',
                'see extension-bundle',
                'list extension-bundle',
                'view extension-bundle',
                'create extension-bundle',
                'update extension-bundle',
                'delete extension-bundle',
                'submit extension-bundle',
                'see extension-analytic',
                'view extension-analytic',
                'list extension-analytic',
                'see extension-payment',
                'view extension-payment',
                'list extension-payment',
                'list extension',
            ],
        ],
    ];

    /**
     * Roles provided by this schema.
     */
    public array $roles = [
        [
            'name'        => 'Extension Developer',
            'description' => 'Role for developing and publishing extensions.',
            'policies'    => [
                'ExtensionDeveloper',
            ],
        ],
        [
            'name'           => 'Quality Assurance',
            'description'    => 'Role for testing extensions.',
            'permissions'    => [
                'see extension',
                'list extension',
                'view extension',
                'see extension-bundle',
                'list extension-bundle',
                'view extension-bundle',
                'create extension-bundle',
                'update extension-bundle',
                'delete extension-bundle',
                'submit extension-bundle',
            ],
        ],
        [
            'name'           => 'Extension Auditor',
            'description'    => 'Role for auditing and creating reports for extensions.',
            'permissions'    => [
                'see extension',
                'list extension',
                'view extension',
                'see extension-bundle',
                'list extension-bundle',
                'view extension-bundle',
                'see extension-analytic',
                'list extension-analytic',
                'view extension-analytic',
                'see extension-payment',
                'list extension-payment',
                'view extension-payment',
            ],
        ],
        [
            'name'           => 'Extension Admin',
            'description'    => 'Role for full control of extensions.',
            'permissions'    => [
                '* extension',
                '* extension-bundle',
                '* extension-analytic',
                '* extension-payment',
                '* extension-purchase',
                '* extension-install',
                '* registry-token',
            ],
        ],
    ];
}
