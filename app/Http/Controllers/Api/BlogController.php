<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Blog;
use Illuminate\Http\Request;
use App\Services\CacheService;
use App\Http\Requests\BlogRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BlogController extends Controller
{

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $blogs = Blog::select('id', 'title', 'image', 'description')->latest()->paginate(10);
        $cacheKey = 'blogs';

        $this->cacheService->storeCache($cacheKey, $blogs);

        $blogs = $this->cacheService->getCache($cacheKey);

        foreach ($blogs as $blog) {
            $blog->image = asset('storage/'.$blog->image);
        }

        if(!empty($blogs)) {
            return $this->successResponse('Get Data Successfully', $blogs);
        } else {
            return $this->nullResponse("There's no Blog Data", NULL);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BlogRequest $request)
    {
        $fileName = image_store('blog_image', $request->file('image'));

        Blog::insert([
            'title' => $request->title,
            'image' => $fileName,
            'description' => $request->description,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $this->successResponse('Data Created Successfully', NULL);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $cacheKey = 'blogs';

        $blog = $this->cacheService->getCache($cacheKey);

        if($blog) {
            $blog = $blog->select('id', 'title', 'image', 'description')->find($id);
        } else {
            $blog = Blog::find($id);
        }

        if(!empty($blog)) {
            return $this->successResponse('Data Found', $blog);
        } else {
            return $this->failResponse("There's no data", NULL);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(BlogRequest $request, $id)
    {
        $blog = Blog::find($id);

        if(!empty($blog)) {
            if($request->file('image')) {
                image_delete('/blog_image/', $blog->image);

                $fileName = image_store('blog_image', $request->file('image'));

                $blog->update([
                    'title' => $request->title,
                    'image' => $fileName,
                    'description' => $request->description,
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $blog->update([
                    'title' => $request->title,
                    'description' => $request->description,
                    'updated_at' => Carbon::now(),
                ]);
            }
            return $this->successResponse('Blog Updated Successfully!', $blog);
        } else {
            return $this->nullResponse("There's no Blog Data", NULL);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $blog = Blog::find($id);

        if(!empty($blog)) {
            if(image_delete('/blog_image/', $blog->image)){

                $blog->delete();

                return $this->successResponse('Blog Deleted Successfully!', NULL);
            } else{
                return $this->failResponse('Blog Image Error! Please Contact to Backend Team', NULL);
            }
        } else {
            return nullResponse("There's no Blog Data", NULL);
        }
    }

    /**
     * Total Blog
     */
    public function total()
    {
        $total = Blog::count();

        if(!empty($total)) {
            return $this->successResponse('Total Blog Get Successfully!', $total);
        } else {
            return $this->nullResponse("There's no Blog Data", NULL);
        }
    }

    /**
     * Search blog with name
     */
    public function searchByName(Request $request)
    {
        $blogs = Blog::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $blogs = search_by_name($blogs, $key);

            if(!empty($blogs->items())) {
                return $this->successResponse('Blog Found Successfully!', $blogs);
            } else {
                return $this->nullResponse("There's no Blog Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Filter by date
     */
    public function filterByDate(Request $request)
    {
        $blogs = Blog::query();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $blogs = filter_by_date($blogs, $startDate, $endDate);

            if(!empty($blogs->items())) {
                return $this->successResponse('Blog Found Successfully!', $blogs);
            } else {
                return $this->nullResponse("There's no Blog Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Sort by name
     */
    public function sortByName(Request $request)
    {
        $blogs = Blog::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $blogs = sort_by_name($blogs, $key);

            if(!empty($blogs->items())) {
                return $this->successResponse('Blog Found Successfully!', $blogs);
            } else {
                return $this->nullResponse("There's no Blog Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Delete all blogs
     */
    public function multipleDelete(Request $request)
    {
        $blogs = Blog::query();

        $ids = $request->input('ids');
        $ids = explode(',', $ids);

        if(!empty($request->input()) && $ids != NULL) {
            multiple_delete($blogs, $ids, '/blog_image/');

            return $this->successResponse('Multiple Blogs Deleted Successfully!', NULL);
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }
}
