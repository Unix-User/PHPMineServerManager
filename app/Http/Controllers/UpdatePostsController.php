<?php

namespace App\Http\Controllers;

use App\Models\UpdatePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;

class UpdatePostsController extends Controller
{
    protected $rules = [
        'title' => 'required',
        'content' => 'required',
        'post_image_path' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ];

    public function index()
    {
        $updatePosts = Cache::get('updatePosts', fn() => UpdatePost::all());
        return Inertia::render('UpdatePosts', ['updatePosts' => $updatePosts]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->rules);
        $validatedData['author_id'] = $request->user()->id;
        
        
        Log::info('UPDATEPOST: ' . json_encode($validatedData));
        $image = $request->file('post_image_path');
        if($image){
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = Storage::disk('public')->put('post-image-photos/' . $fileName, file_get_contents($image));
            $validatedData['post_image_path'] = $imagePath;
        }

        $updatePost = UpdatePost::create($validatedData);
        Cache::forget('updatePosts');
        return redirect()->route('updatePosts.index');
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return \Inertia\Response
     */
    public function show(int $id)
    {
        $updatePost = Cache::get('updatePost_' . $id, fn() => UpdatePost::findOrFail($id));
        return Inertia::render('UpdatePosts/Show', ['updatePost' => $updatePost]);
    }

    public function update(Request $request, int $id)
    {
        $updatePost = UpdatePost::findOrFail($id);
        $validatedData = $request->validate($this->rules);
        $image = $request->file('post_image_path');
        if($image){
            $fileName = time() . '.' . $image->getClientOriginalExtension();
            $imagePath = Storage::disk('public')->put('post-photos/' . $fileName, file_get_contents($image));
            $validatedData['post_image_path'] = $imagePath;
        } else if($request->input('remove_image')) {
            $validatedData['post_image_path'] = null;
        }

        $updatePost->update($validatedData);
        Cache::forget('updatePosts');
        Cache::forget('updatePost_' . $id);
        return redirect()->route('updatePosts.index');
    }

    public function destroy(int $id)
    {
        $updatePost = UpdatePost::findOrFail($id);
        $updatePost->delete();
        Cache::forget('updatePosts');
        Cache::forget('updatePost_' . $id);
        return redirect()->route('updatePosts.index');
    }
}

