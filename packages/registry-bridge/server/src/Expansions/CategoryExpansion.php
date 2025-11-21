<?php

namespace Fleetbase\RegistryBridge\Expansions;

use Fleetbase\Build\Expansion;
use Fleetbase\RegistryBridge\Models\RegistryExtension;

class CategoryExpansion implements Expansion
{
    /**
     * Get the target class to expand.
     *
     * @return string|Class
     */
    public static function target()
    {
        return \Fleetbase\Models\Category::class;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public static function registryExtensions()
    {
        return function () {
            /* @var \Illuminate\Database\Eloquent\Model $this */
            return $this->hasMany(RegistryExtension::class, 'category_uuid', 'uuid')->where('status', 'published');
        };
    }
}
