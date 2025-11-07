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
            ->when(request('sport'), function ($query) {
                $query->where('sport', request('sport'));
            })
            ->orderBy('sport')
            ->orderBy('order')
            ->orderBy('name')
            ->paginate(20)
            ->appends(request()->only('sport'));

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
        // Get the next order number for this sport
        $maxOrder = Category::where('sport', $request->sport)->max('order') ?? -1;

        Category::create([
            'name' => $request->name,
            'icon' => $request->icon,
            'order' => $maxOrder + 1,
            'is_active' => $request->boolean('is_active', true),
            'sport' => $request->sport,
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
        // Prevent editing categories in active games
        if (! $category->canBeEdited()) {
            return back()->with('error', 'Αυτή η κατηγορία χρησιμοποιείται σε ενεργά παιχνίδια και δεν μπορεί να επεξεργαστεί.');
        }

        $category->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'is_active' => $request->boolean('is_active', true),
            'sport' => $request->sport,
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

        // Prevent deleting categories in active games
        if (! $category->canBeDeleted()) {
            return redirect()->route('categories.index')
                ->with('error', 'Αυτή η κατηγορία χρησιμοποιείται σε ενεργά παιχνίδια και δεν μπορεί να διαγραφεί.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully');
    }
}
