<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Medicine;

class MedicineController extends Controller
{
    public function index(Request $request)
    {
        $medicines = Medicine::where('is_active', true)
            ->with(['medicineCategory:id,name', 'images'])
            ->orderBy('name')
            ->paginate(12);

        return view('patient.medicines.index', compact('medicines'));
    }

    public function show(Medicine $medicine)
    {
        if (! $medicine->is_active) {
            abort(404);
        }

        $medicine->load(['medicineCategory:id,name', 'images']);

        return view('patient.medicines.show', compact('medicine'));
    }
}
