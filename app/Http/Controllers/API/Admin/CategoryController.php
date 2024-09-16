<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:categories,name|min:3',
            'description' => 'max:300',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        $data = $request->all();
        $data['slug'] = Str::slug($request['name']);

        try {
            $post = Category::create($data);
            return response()->json([
                'message' => 'Category added successfully',
                'category' => $post
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Fail to add category',
                'error_log' => $e->getMessage()
            ], 400);
        }
    }
}