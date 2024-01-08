<?php

namespace App\Services;

use App\Models\Blog;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    public function storeCache($key, $value, $minutes = 10)
    {
        Cache::put($key, $value, $minutes);
    }

    public function getCache($key)
    {
        return Cache::get($key);
    }
}
