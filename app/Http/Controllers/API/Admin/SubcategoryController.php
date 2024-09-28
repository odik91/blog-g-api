<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Subcategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubcategoryController extends Controller
{
    public function getSubcategories(Request $request)
    {
        // ?limit=10&page=1&search=global&category_id=cat&subcategory=subs&orderBy=category_id&order=desc

        $limit = (int) $request->input('limit', 10);
        $search = $request->input('search', null);
        $category = $request->input('category_id', null);
        $subcategory = $request->input('subcategory', null);
        $orderBy = $request->input('orderBy', 'subcategories.subcategory');
        $order = $request->input('order', 'asc');

        $setOrder = null;
        if ($orderBy && $orderBy === 'category_id') {
            $setOrder = 'categories.name';
        } else {
            $setOrder = 'subcategories.subcategory';
        }

        $subcategories = Subcategory::leftJoin('categories', 'subcategories.category_id', 'categories.id')
            ->when($search, function ($query, $search) {
                return $query->where('subcategories.subcategory', 'like', "%" . $search . "%")->orWhere('categories.name', 'like', "%" . $search . "%");
            })
            ->when($category, function ($query, $category) {
                return $query->where('categories.name', 'like', "%" . $category . "%");
            })
            ->when($subcategory, function ($query, $subcategory) {
                return $query->where('categories.name', 'like', "%" . $subcategory . "%");
            })
            ->select(
                'subcategories.id',
                'subcategories.category_id',
                'subcategories.subcategory',
                'subcategories.slug',
                'subcategories.description',
                'subcategories.is_active',
                'categories.name as category_name',
            )

            ->orderBy($setOrder, $order)
            ->paginate($limit);

        return response()->json($subcategories, 200);
    }

    public function createSubcategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subcategory' => 'required|min:3|max:300|unique:subcategories,subcategory,category_id',
            'category_id' => 'required',
            'is_active' => 'required',
            'description' => 'max:300',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $data = $request->all();
        $data['slug'] = Str::slug($request['subcategory']);

        try {
            $subcategory = Subcategory::create($data);
            return response()->json([
                'message' => 'Subcategory added successfully',
                'subcategory' => $subcategory
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Fail to add subcategory',
                'error_log' => $e->getMessage()
            ], 400);
        }
    }

    public function getSubcategory($id)
    {
        try {
            $subcategory = Subcategory::with('getCategory')->find($id);
            return response()->json([
                'message' => 'Success!',
                'subcategory' => $subcategory
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Subcategory not found',
                'error_log' => $e->getMessage()
            ], 404);
        }
    }

    public function updateSubcategory(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'subcategory' => [
                'required',
                'min:3',
                'max:300',
                Rule::unique('subcategories')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id);
                })->ignore($id),
            ],
            'category_id' => 'required',
            'description' => 'max:300',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            $subcategory = Subcategory::find($id);
            $data = [
                'category_id' => $request['category_id'],
                'subcategory' => $request['subcategory'],
                'description' => $request['description'],
                'is_active' => $request['is_active'],
                'slug' => Str::slug($request['subcategory'])
            ];
            $subcategory->update($data);
            return response()->json([
                'message' => "Subcategory $request->subcategory updated successfully"
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Fail to update subcategory',
                'error_log' => $e->getMessage()
            ], 500);
        }
    }

    public function massUpdateSubcategory(Request $request)
    {
        foreach ($request->all() as $updateData) {
            // return response()->json($updateData, 200);
            $validator = Validator::make($updateData, [
                'id' => 'required|exists:subcategories,id',
                'category_id' => 'required|exists:categories,id',
                'subcategory' => [
                    'required',
                    'min:3',
                    'max:300',
                    Rule::unique('subcategories')->where(function ($query) use ($updateData) {
                        return $query->where('category_id', $updateData['category_id']);
                    })->ignore($updateData['id']),
                ],
                'description' => 'max:300',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
        }

        DB::beginTransaction();
        try {
            $message = '';
            $data = $request->all();
            foreach ($data as $key => $item) {
                // Cari subcategory berdasarkan ID
                $subcategory = Subcategory::find($item['id']);

                if ($subcategory) {
                    // Update subcategory
                    $subcategory->update([
                        'category_id' => $item['category_id'],
                        'subcategory' => $item['subcategory'],
                        'description' => $item['description'],
                        'is_active' => $item['is_active'],
                        'slug' => Str::slug($item['subcategory'])
                    ]);

                    // Format pesan sukses
                    if ($key == 0) {
                        $message .= ucfirst($subcategory['subcategory']) . ', ';
                    } elseif ($key == count($data) - 1) {
                        $message .= 'and ' . $subcategory['subcategory'];
                    } else {
                        $message .= $subcategory['subcategory'] . ', ';
                    }
                } else {
                    throw new Exception("Subcategory ID {$item['id']} not found");
                }
            }

            DB::commit();
            return response()->json([
                'message' => $message . ' edited successfully.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteSubcategory($id)
    {
        $subcategory = Subcategory::find($id);
        if ($subcategory) {
            $name = $subcategory['subcategory'];
            try {
                $subcategory->delete();
                return response()->json([
                    'message' => "Subcategory {$name} deleted successfully"
                ], 200);
            } catch (Exception $e) {
                return response()->json([
                    'message' => "Fail to delete subcategory $name",
                    'error_log' => $e->getMessage(),
                ], 500);
            }
        }
    }
}