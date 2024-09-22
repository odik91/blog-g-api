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
            return response()->json($validator->errors(), 400);
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

    public function getCategory(Request $request)
    {
        $order = $request->input('order', 'asc');
        $name = $request->input('search', null);
        $limit = (int) $request->input('limit', 10);
        $orderBy = $request->input('orderBy', 'name');
        $findColumn = $request->input('searchData', null);
        $findValue = $request->input('value', null);

        $findData = null;

        if ($findColumn && $findValue) {
            $findData = [
                'search' => $findColumn,
                'value' => $findValue,
            ];
        }

        $categories = Category::query()
            ->when($name, function ($query, $name) {
                return $query->where('name', 'like', "%" . $name . "%");
            })
            ->when($findData, function ($query, $findData) {
                return $query->where($findData['search'], 'like', "%" . $findData['value'] . "%");
            })
            ->orderBy($orderBy, $order)
            ->paginate($limit);

        return response()->json([
            'message' => 'success',
            'categories' => $categories
        ], 200);
    }

    public function editCategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:categories,name,' . $id,
            'description' => 'max:300',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $category = Category::find($id);
            $data = [
                'name' => $request['name'],
                'description' => $request['description'],
                'slug' => Str::slug($request['name'])
            ];
            $category->update($data);
            return response()->json([
                'message' => 'Category ' . $data['name'] . ' updated successfully',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Fail to update ' . $request['name'],
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function getSingleCategory($id)
    {
        $category = Category::find($id);
        return response()->json([
            'message' => "Successs",
            'category' => $category
        ], 200);
    }

    public function deleteCategory($id)
    {
        $category = Category::find($id);
        $name = $category['name'];
        try {
            $category->delete();
            return response()->json([
                'message' => "Category {$name} deleted successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Fail to delete {$name}",
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function getTrashCategory(Request $request)
    {        
        $order = $request->input('order', 'asc');
        $name = $request->input('search', null);
        $limit = (int) $request->input('limit', 10);
        $orderBy = $request->input('orderBy', 'name');
        $findColumn = $request->input('searchData', null);
        $findValue = $request->input('value', null);

        $findData = null;

        if ($findColumn && $findValue) {
            $findData = [
                'search' => $findColumn,
                'value' => $findValue,
            ];
        }

        $categories = Category::onlyTrashed()
            ->when($name, function ($query, $name) {
                return $query->where('name', 'like', "%" . $name . "%");
            })
            ->when($findData, function ($query, $findData) {
                return $query->where($findData['search'], 'like', "%" . $findData['value'] . "%");
            })
            ->orderBy($orderBy, $order)
            ->paginate($limit);

        return response()->json([
            'message' => 'success',
            'categories' => $categories
        ], 200);
    }

    public function restoreCategory($id)
    {
        $category = Category::onlyTrashed()->where('id', $id)->first();
        $name = $category['name'];
        try {
            Category::onlyTrashed()->where('id', $id)->restore();
            return response()->json([
                'message' => "Category {$name} restored successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Fail to restore {$name}",
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function destroyCategory($id)
    {
        $category = Category::onlyTrashed()->where('id', $id)->first();
        $name = $category['name'];
        try {
            Category::onlyTrashed()->where('id', $id)->forceDelete();
            return response()->json([
                'message' => "Category {$name} has been destroyed"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => "Fail to destroy {$name}",
                'error_log' => $e->getMessage()
            ], 500);
        }
    }
}