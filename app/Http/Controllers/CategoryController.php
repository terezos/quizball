<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        $categories = Category::query()
            ->withCount('questions')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20);

        return view('categories.index', ['categories' => $categories]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
        Category::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'order' => $request->order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('categories.edit', ['category' => $category]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $category->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'order' => $request->order,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403);
        }

        // Check if category has questions
        if ($category->questions()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete category that has questions assigned to it');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
