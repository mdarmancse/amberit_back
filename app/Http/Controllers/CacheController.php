<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class CacheController extends Controller
{
    public function fetchData($key)
    {
        $data = Cache::remember($key, $minutes, function () {
            // Fetch data from the source if not in cache
            return $this->getDataFromSource();
        });

        return response()->json($data);
    }

    private function getDataFromSource()
    {
        // Perform actual data retrieval from the source (e.g., API, database)
        // ...

        // Store data in cache for future use
        Cache::put($key, $data, $minutes);

        return $data;
    }
}
