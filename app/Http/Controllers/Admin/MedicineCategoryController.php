<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMedicineCategoryRequest;
use App\Http\Requests\Admin\UpdateMedicineCategoryRequest;
use App\Models\MedicineCategory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MedicineCategoryController extends Controller
{
    public function index(): View
    {
        $categories = MedicineCategory::query()
            ->withCount('medicines')
            ->latest()
            ->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreMedicineCategoryRequest $request): RedirectResponse
    {
        MedicineCategory::create($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(MedicineCategory $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function toggleStatus(Request $request, MedicineCategory $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category status updated successfully.');
    }

    public function update(UpdateMedicineCategoryRequest $request, MedicineCategory $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(MedicineCategory $category): RedirectResponse
    {
        if ($category->medicines()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'This category is already linked to medicines and cannot be deleted.');
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
