<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrescriptionController extends Controller
{
    public function index(Request $request): View
    {
        $prescriptions = Prescription::with(['doctor', 'items.medicine', 'appointment'])
            ->where('patient_id', $request->user()->id)
            ->latest()
            ->paginate(10);

        return view('patient.prescriptions.index', compact('prescriptions'));
    }

    public function show(Request $request, Prescription $prescription): View
    {
        abort_unless($prescription->patient_id === $request->user()->id, 403);

        $prescription->load(['doctor', 'items.medicine', 'appointment']);

        return view('patient.prescriptions.show', compact('prescription'));
    }
}
