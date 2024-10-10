<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class PostController extends Controller
{
    private function _uploadImage($file, $title)
    {
        $dateFolder = now()->format('Y-m-d');
        $imageName = strtolower(Str::slug($title)) . '.' . $file->getClientOriginalExtension();

        $filePath = $file->storeAs('uploads/' . $dateFolder, $imageName, 'public');
        return $filePath;
    }

    public function posting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'title' => [
                'required',
                'min:5',
                'max: 300',
                Rule::unique("posts")->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id)->where('subcategory_id', $request->subcategory_id);
                })
            ],
            'image' => 'required|image|mimes:jpeg,png,jpg,gif',
            'meta_description' => 'required|min:5|max:300',
            'meta_keyword' => 'required',
            'seo_title' => 'required|min:5|max:300',
            'content' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        }

        try {
            $file = $request['image'];
            $dateFolder = now()->format('Y-m-d');
            $imageName = strtolower(Str::slug($request['title'])) . '.' . $file->getClientOriginalExtension();

            $filePath = $file->storeAs('uploads/' . $dateFolder, $imageName, 'public');

            $data = $request->all();
            $data['image'] = '/storage/' . $filePath;
            $data['category_id'] = (int) $request['category_id'];
            $data['slug'] = Str::slug($request['title']);
            $data['is_active'] = $request['is_active'] === 'active' ? true : false;
            $data['created_by'] = $request->user()->id;

            $post = Post::create($data);
            return response()->json([
                'message' => "Post $post->title added successfully",
                'post' => $post
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Fail to create post',
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function getPosts(Request $request)
    {
        $limit = (int) $request->input('limit', 10);
        $order = $request->input('order', 'asc');
        $orderBy = $request->input('orderBy', 'title');

        $posts = Post::with('getCategory')
            ->with('getSubcategory')
            ->with('getUser')
            ->orderBy("posts." . $orderBy, $order)
            ->paginate($limit);
        return response()->json($posts, 200);
    }

    public function getSinglePost($id)
    {
        try {
            $post = Post::with('getCategory')->with('getSubcategory')->with('getUser')->find($id);
            return response()->json($post, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Fail to create post',
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function editPost(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required',
            'subcategory_id' => 'required',
            'title' => [
                'required',
                'min:5',
                'max: 300',
                Rule::unique("posts")->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id)->where('subcategory_id', $request->subcategory_id);
                })->ignore($id)
            ],
            'image' => 'image|mimes:jpeg,png,jpg,gif',
            'meta_description' => 'required|min:5|max:300',
            'meta_keyword' => 'required',
            'seo_title' => 'required|min:5|max:300',
            'content' => 'required',
            'is_active' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        }

        try {
            $post = Post::find($id);
            $data = $request->all();

            $file = $request['image'];
            $filePath = "";
            if ($file) {
                // test delete old image
                unlink(public_path($post['image']));

                $filePath = $this->_uploadImage($file, $request['title']);
                $data['image'] = '/storage/' . $filePath;
            }

            $data['category_id'] = (int) $request['category_id'];
            $data['slug'] = Str::slug($request['title']);
            $data['is_active'] = $request['is_active'] === 'active' ? true : false;
            $data['created_by'] = $request->user()->id;

            $post->update($data);
            return response()->json([
                'message' => "Post $post->title updated successfully",
                'post' => $post,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Fail to create post',
                'error_log' => $e->getMessage()
            ], 500);
        }
    }
}