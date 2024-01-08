<?php

if(!function_exists('image_store')) {
    /**
     *  Store image
     */
    function image_store($path, $image)
    {
        $fileName = uniqid().'_'.$image->getClientOriginalName();

        $filePath = Storage::disk('public')->putFileAs($path, $image, $fileName);

        return $filePath;
    }
}

if(!function_exists('image_delete')) {
    /**
     * Delete image from storage
     */
    function image_delete($path, $fileName)
    {
        if(Storage::disk('public')->exists($fileName)){
            Storage::disk('public')->delete($fileName);

            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('search_by_name')) {
    /**
     * Search something by name
     */
    function search_by_name($query, $key)
    {
        return $query->where('title', 'LIKE', "%".$key."%")->paginate(10);
    }
}

if(!function_exists('filter_by_date')) {
    /**
     * Filter something by date
     */
    function filter_by_date($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate])->paginate(10);
    }
}

if(!function_exists('sort_by_name')) {
    /**
     * Sort something by name
     */
    function sort_by_name($query, $key)
    {
        return $query->orderBy('title', $key)->paginate(10);
    }
}

if(!function_exists('multiple_delete')) {
    /**
     * Multiple delete something
     */
    function multiple_delete($query, $key, $path)
    {
        $fileNames = $query->whereIn('id', $key)->pluck('image');

        $query->whereIn('id', $key)->delete();

        foreach ($fileNames as $fileName) {
            if ($fileName) {
                Storage::disk('public')->delete($path.$fileName);
            }
        }

        return true;
    }
}
