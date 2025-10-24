<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate(['name' => "required|string|max:128"]);

        $category = Category::create($validatedData);

        return response()->json(['message' => 'category created successfuly', 'category' => $category], 201);
    }

    public function update(Request $request, $id)
    {
        try {
            $category = Category::findOrFail($id);
            $validatedData = $request->validate(['name' => "required|string|max:128"]);

            $category->update($validatedData);

            return response()->json(['message' => 'category updated successfuly', 'category' => $category], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 401);
        } catch (Exception $e) {
            return response()->json('error happened when updating', 401);
        }
    }

    public function destroy($id)
    {
        try {
            $category = Category::findOrFail($id);
            $category->delete();

            return response()->json(['message' => 'Category Has been Deleted Successfuly'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'id not found', 'message' => $e->getMessage()], 401);
        } catch (Exception $e) {
            return response()->json('error happened when updating', 401);
        }
    }

    public function show()
    {
        $categories = Category::all();
        return response()->json(['categories' => $categories], 200);
    }
}
