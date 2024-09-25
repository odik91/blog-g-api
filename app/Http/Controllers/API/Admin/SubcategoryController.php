<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            'name' => 'required|unique:categories,name|min:3',
            'description' => 'max:300',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
    }
}