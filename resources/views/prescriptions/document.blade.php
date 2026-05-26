@php
    $showCartActions = $showCartActions ?? false;
    $appName = config('app.name');
    $doctor = $prescription->doctor;
    $patient = $prescription->patient;
    $doctorProfile = $doctor?->doctorProfile;
    $patientProfile = $patient?->patientProfile;
    $appointment = $prescription->appointment;
    $reference = 'RX-'.str_pad((string) $prescription->id, 6, '0', STR_PAD_LEFT);
    $issuedAt = $prescription->created_at?->format('l, F j, Y h:i A');
    $patientDetails = collect([
        $patientProfile?->gender ? ucfirst($patientProfile->gender) : null,
        filled($patientProfile?->age) ? $patientProfile->age.' years' : null,
        $patient?->email,
    ])->filter()->join(', ');
    $doctorCredentials = collect([
        $doctorProfile?->specialization,
        $doctorProfile?->qualification,
    ])->filter()->join(', ');
@endphp

@once
    @push('styles')
        <style>
            .prescription-document {
                min-width: 900px;
                color: #0f172a;
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .prescription-sheet {
                font-family: inherit;
            }

            .prescription-table th,
            .prescription-table td {
                border: 1px solid #cbd5e1;
            }

            @media print {
                @page {
                    size: A4;
                    margin: 10mm;
                }

                .prescription-document {
                    width: 100% !important;
                    min-width: 0 !important;
                    max-width: none !important;
                    box-shadow: none !important;
                    border: 0 !important;
                    background: #ffffff !important;
                    color: #0f172a !important;
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }

                .prescription-document * {
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }

                .prescription-table {
                    min-width: 0 !important;
                }

                .prescription-header,
                .prescription-patient-line,
                .prescription-footer {
                    break-inside: avoid;
                }
            }
        </style>
    @endpush
@endonce

<article class="prescription-document print-area overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-white p-6">
    <div class="prescription-sheet bg-white text-slate-950">
        <header class="prescription-header flex items-start justify-between gap-8 border-b border-slate-200 pb-3">
            <div class="max-w-xl">
                <h1 class="text-base font-bold tracking-tight text-slate-950">{{ $doctor?->name ? 'Dr. '.$doctor->name : 'Doctor' }}</h1>
                @if($doctorCredentials)
                    <p class="mt-1 text-sm font-semibold text-blue-700">{{ $doctorCredentials }}</p>
                @endif
                <div class="mt-1 space-y-0.5 text-sm leading-5 text-slate-500">
                    @if(filled($doctorProfile?->experience_years))
                        <p>{{ $doctorProfile->experience_years }} years experience</p>
                    @endif
                    @if($doctor?->email)
                        <p class="break-all">{{ $doctor->email }}</p>
                    @endif
                </div>
            </div>

            <div class="shrink-0 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-slate-950 shadow-sm">
                <div class="flex items-center gap-3">
                    <x-logo size="38" :showText="false" class="rounded-lg bg-white p-1 shadow-sm ring-1 ring-slate-200" />
                    <div class="text-right">
                        <p class="text-xl font-bold leading-5 tracking-tight text-slate-950">{{ $appName }}</p>
                        <p class="mt-1 text-[10px] font-semibold uppercase tracking-[0.18em] text-emerald-600">Intelligent Care. Human Touch.</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="prescription-patient-line mt-3 flex items-start justify-between gap-5 text-sm text-slate-600">
            <p class="leading-5">
                <span class="font-bold text-slate-950">{{ $patient?->name ?? 'Patient' }}</span>@if($patientDetails), {{ $patientDetails }}@endif
            </p>
            <p class="shrink-0 text-right font-semibold text-slate-500">{{ $issuedAt }}</p>
        </div>

        <section class="mt-7">
            <div class="mb-2 flex items-end justify-between gap-4">
                <h2 class="flex-1 text-center text-xl font-bold uppercase tracking-tight text-blue-700">Prescription</h2>
                <p class="text-xs font-bold text-slate-500">{{ $reference }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="prescription-table w-full min-w-[760px] border-collapse text-left text-sm leading-5">
                    <thead class="bg-blue-50 text-slate-700">
                        <tr>
                            <th class="w-10 px-2 py-1.5 text-center">#</th>
                            <th class="px-2 py-1.5 text-center">Medicine</th>
                            <th class="w-32 px-2 py-1.5 text-center">Dose</th>
                            <th class="w-32 px-2 py-1.5 text-center">Duration</th>
                            <th class="px-2 py-1.5 text-center">Instructions</th>
                            @if($showCartActions)
                                <th class="no-print w-28 px-2 py-1.5 text-center">Order</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescription->items as $item)
                            <tr class="align-top">
                                <td class="px-2 py-2 text-center font-bold text-blue-700">{{ $loop->iteration }}</td>
                                <td class="px-2 py-2">
                                    <span class="font-bold text-slate-950">{{ $item->medicine?->name ?? 'Medicine' }}</span>
                                </td>
                                <td class="px-2 py-2 text-center text-slate-700">{{ $item->dosage ?: '-' }}</td>
                                <td class="px-2 py-2 text-center text-slate-700">{{ $item->duration ?: '-' }}</td>
                                <td class="px-2 py-2 text-slate-700">{{ $item->instructions ?: '-' }}</td>
                                @if($showCartActions)
                                    <td class="no-print px-2 py-2 text-center">
                                        @if($item->medicine_id)
                                            <form method="POST" action="{{ route('patient.cart.add') }}" class="inline-flex">
                                                @csrf
                                                <input type="hidden" name="medicine_id" value="{{ $item->medicine_id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button class="rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700" type="submit">Add</button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showCartActions ? 6 : 5 }}" class="px-4 py-8 text-center text-slate-500">No medicines listed.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($appointment?->symptoms)
            <section class="mt-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Symptoms:</h3>
                <p class="mt-3 text-sm font-medium leading-6 text-slate-700">{{ $appointment->symptoms }}</p>
            </section>
        @endif

        @if($prescription->diagnosis)
            <section class="mt-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Diagnosis:</h3>
                <p class="mt-3 text-sm font-medium leading-6 text-slate-700">{{ $prescription->diagnosis }}</p>
            </section>
        @endif

        @if($appointment?->advice || $prescription->notes)
            <section class="mt-6">
                <h3 class="text-sm font-bold uppercase tracking-wide text-blue-700">Notes:</h3>
                <div class="mt-3 space-y-2 text-sm leading-6 text-slate-700">
                    @if($appointment?->advice)
                        <p>{{ $appointment->advice }}</p>
                    @endif
                    @if($prescription->notes)
                        <p>{{ $prescription->notes }}</p>
                    @endif
                </div>
            </section>
        @endif

        <footer class="prescription-footer mt-10 grid grid-cols-[1fr_15rem] gap-8 text-sm">
            <p class="leading-6 text-slate-500">Take all medications as directed. Contact your doctor immediately if symptoms worsen or if you experience side effects.</p>
            <div class="text-right">
                <div class="ml-auto h-14 w-44 border-b border-slate-300"></div>
                <p class="mt-2 font-bold text-slate-950">{{ $doctor?->name ? 'Dr. '.$doctor->name : 'Doctor' }}</p>
                @if($doctorCredentials)
                    <p class="text-xs text-slate-500">{{ $doctorCredentials }}</p>
                @endif
            </div>
        </footer>
    </div>
</article>
