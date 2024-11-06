<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Botble\Ecommerce\Models\ProductCategory;

class CategoryController extends Controller
{
    // Fetch and list categories in a parent-child structure
    public function index(Request $request)
    {
        // Fetch all categories
        $categories = ProductCategory::all();

        // Transform categories into a parent-child structure
        $categoriesTree = $this->buildTree($categories);

        return response()->json($categoriesTree);
    }

    // Show a single category by ID
    public function show($id)
    {
        // Fetch the category only (no products or children)
        $category = ProductCategory::findOrFail($id);

        // Optionally include slug in response if needed
        $category->slug = $category->slug;

        return response()->json([
            'category' => $category,
        ]);
    }

    // Store a new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:ec_product_categories,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image' => 'nullable|string',
            'is_featured' => 'required|boolean',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category = ProductCategory::create($validated);
        return response()->json($category, 201);
    }

    // Update an existing category
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:ec_product_categories,id',
            'description' => 'nullable|string',
            'status' => 'required|boolean',
            'image' => 'nullable|string',
            'is_featured' => 'required|boolean',
            'icon' => 'nullable|string',
            'icon_image' => 'nullable|string',
            'order' => 'nullable|integer',
        ]);

        $category = ProductCategory::findOrFail($id);
        $category->update($validated);
        return response()->json($category);
    }

    // Delete a category
    public function destroy($id)
    {
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Category deleted successfully']);
    }

    // Build a tree structure from categories
    private function buildTree($categories, $parentId = 0)
    {
        $branch = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $children = $this->buildTree($categories, $category->id);
                if ($children) {
                    $category->children = $children;
                } else {
                    $category->children = [];
                }
                $branch[] = $category;
            }
        }

        return $branch;
    }
}
