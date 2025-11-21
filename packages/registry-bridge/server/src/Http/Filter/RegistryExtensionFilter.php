<?php

namespace Fleetbase\RegistryBridge\Http\Filter;

use Fleetbase\Http\Filter\Filter;
use Fleetbase\RegistryBridge\Support\Utils;
use Illuminate\Support\Str;

class RegistryExtensionFilter extends Filter
{
    public function queryForInternal()
    {
        if ($this->request->boolean('explore') || $this->request->boolean('admin')) {
            return;
        }
        $this->builder->where('company_uuid', $this->session->get('company'));
    }

    public function queryForPublic()
    {
        $this->builder->where('company_uuid', $this->session->get('company'));
    }

    public function admin()
    {
        $user = $this->request->user();
        if ($user && $user->isNotAdmin()) {
            $this->builder->where('company_uuid', $this->session->get('company'));
        }
    }

    public function query(?string $searchQuery)
    {
        $this->builder->search($searchQuery);
    }

    public function isAuthor()
    {
        $this->builder->where('company_uuid', session('company'));
    }

    public function explore()
    {
        $this->builder->where('status', 'published');
        $this->builder->without(['current_bundle', 'next_bundle', 'category']);
        $this->builder->with(['screenshots']);
    }

    public function category($category)
    {
        if (Str::isUuid($category)) {
            return $this->builder->where('category_uuid', $category);
        }

        if (Utils::isPublicId($category)) {
            return $this->builder->whereHas('category', function ($query) use ($category) {
                $query->where('public_id', $category)->where('for', 'extension_category')->where('core_category', 1);
            });
        }

        // assume slug
        return $this->builder->whereHas('category', function ($query) use ($category) {
            $query->where('slug', $category)->where('for', 'extension_category')->where('core_category', 1);
        });
    }

    public function createdAt($createdAt)
    {
        $createdAt = Utils::dateRange($createdAt);

        if (is_array($createdAt)) {
            $this->builder->whereBetween('created_at', $createdAt);
        } else {
            $this->builder->whereDate('created_at', $createdAt);
        }
    }

    public function updatedAt($updatedAt)
    {
        $updatedAt = Utils::dateRange($updatedAt);

        if (is_array($updatedAt)) {
            $this->builder->whereBetween('updated_at', $updatedAt);
        } else {
            $this->builder->whereDate('updated_at', $updatedAt);
        }
    }
}
