<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMedicineRequest;
use App\Http\Requests\Admin\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\MedicineImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class MedicineController extends Controller
{
    public function index(): View
    {
        $medicines = Medicine::query()
            ->with(['medicineCategory:id,name', 'images'])
            ->latest()
            ->get();

        return view('admin.medicines.index', compact('medicines'));
    }

    public function create(): View
    {
        $categories = MedicineCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.medicines.create', compact('categories'));
    }

    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $category = MedicineCategory::findOrFail($data['category_id']);

        DB::transaction(function () use ($request, $data, $category) {
            $medicine = Medicine::create([
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'composition' => $data['composition'] ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'expiry_date' => $data['expiry_date'],
                'sku' => $data['sku'],
                'category_id' => $category->id,
                'category' => $category->name,
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->storeUploadedImages(
                $medicine,
                $request->file('image') ? [$request->file('image')] : [],
                true
            );
            $this->storeUploadedImages($medicine, $request->file('images', []));
            $this->syncThumbnailColumn($medicine);
        });

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine created successfully.');
    }

    public function edit(Medicine $medicine): View
    {
        $medicine->load(['images', 'medicineCategory:id,name']);

        $categories = MedicineCategory::query()
            ->where('is_active', true)
            ->orWhere('id', $medicine->category_id)
            ->orderBy('name')
            ->get();

        return view('admin.medicines.edit', compact('medicine', 'categories'));
    }

    public function show(Medicine $medicine): View
    {
        $medicine->load(['medicineCategory:id,name', 'images']);

        return view('admin.medicines.show', compact('medicine'));
    }

    public function toggleStatus(Request $request, Medicine $medicine): RedirectResponse
    {
        $medicine->update([
            'is_active' => ! $medicine->is_active,
        ]);

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine status updated successfully.');
    }

    public function update(UpdateMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $data = $request->validated();
        $category = MedicineCategory::findOrFail($data['category_id']);

        DB::transaction(function () use ($request, $medicine, $data, $category) {
            $medicine->update([
                'name' => $data['name'],
                'brand' => $data['brand'] ?? null,
                'description' => $data['description'] ?? null,
                'composition' => $data['composition'] ?? null,
                'manufacturer' => $data['manufacturer'] ?? null,
                'price' => $data['price'],
                'stock_quantity' => $data['stock'],
                'expiry_date' => $data['expiry_date'],
                'sku' => $data['sku'],
                'category_id' => $category->id,
                'category' => $category->name,
                'is_active' => $data['is_active'] ?? false,
            ]);

            $this->removeSelectedImages($medicine, $data['remove_image_ids'] ?? []);
            $this->storeUploadedImages(
                $medicine,
                $request->file('image') ? [$request->file('image')] : [],
                true
            );
            $this->storeUploadedImages($medicine, $request->file('images', []));
            $this->setThumbnail($medicine, $data['thumbnail_image_id'] ?? null);
            $this->syncThumbnailColumn($medicine->fresh('images'));
        });

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->load('images');
        $imagePath = $medicine->image;

        try {
            $medicine->delete();
        } catch (QueryException $exception) {
            return redirect()
                ->route('admin.medicines.index')
                ->with('error', 'This medicine is linked to cart or order records and cannot be deleted.');
        }

        foreach ($medicine->images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }
        }

        if ($imagePath && ! str_starts_with($imagePath, 'images/') && Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }

        return redirect()
            ->route('admin.medicines.index')
            ->with('success', 'Medicine deleted successfully.');
    }

    private function storeUploadedImages(Medicine $medicine, array $uploadedImages, bool $forceThumbnail = false): void
    {
        if ($uploadedImages === []) {
            return;
        }

        $hasExistingImages = $medicine->images()->exists();

        foreach ($uploadedImages as $index => $uploadedImage) {
            $medicine->images()->create([
                'image_path' => $uploadedImage->store('medicines', 'public'),
                'is_thumbnail' => $forceThumbnail ? $index === 0 : (! $hasExistingImages && $index === 0),
            ]);
        }

        if ($forceThumbnail && $uploadedImages !== []) {
            $latestImage = $medicine->images()->latest('id')->first();

            if ($latestImage) {
                $medicine->images()->where('id', '!=', $latestImage->id)->update(['is_thumbnail' => false]);
                $latestImage->update(['is_thumbnail' => true]);
            }
        }
    }

    private function removeSelectedImages(Medicine $medicine, array $removeImageIds): void
    {
        if ($removeImageIds === []) {
            return;
        }

        $images = $medicine->images()->whereIn('id', $removeImageIds)->get();

        foreach ($images as $image) {
            if (Storage::disk('public')->exists($image->image_path)) {
                Storage::disk('public')->delete($image->image_path);
            }

            $image->delete();
        }
    }

    private function setThumbnail(Medicine $medicine, ?int $thumbnailImageId): void
    {
        $images = $medicine->images()->get();

        if ($images->isEmpty()) {
            $medicine->update(['image' => null]);
            return;
        }

        $thumbnail = $thumbnailImageId
            ? $images->firstWhere('id', $thumbnailImageId)
            : $images->firstWhere('is_thumbnail', true);

        if (! $thumbnail) {
            $thumbnail = $images->first();
        }

        $medicine->images()->update(['is_thumbnail' => false]);
        $thumbnail->update(['is_thumbnail' => true]);
    }

    private function syncThumbnailColumn(Medicine $medicine): void
    {
        $thumbnailPath = $medicine->images()->where('is_thumbnail', true)->value('image_path')
            ?? $medicine->images()->value('image_path');

        $medicine->update(['image' => $thumbnailPath]);
    }
}
