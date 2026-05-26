@php
    $showCartActions = $showCartActions ?? false;
    $appName = config('app.name');
    $doctor = $prescription->doctor;
    $patient = $prescription->patient;
    $doctorProfile = $doctor?->doctorProfile;
    $patientProfile = $patient?->patientProfile;
    $appointment = $prescription->appointment;
    $reference = 'RX-'.str_pad((string) $prescription->id, 6, '0', STR_PAD_LEFT);
    $issuedAt = $prescription->created_at?->format('d M Y, h:i A');
    $consultationDate = $appointment?->appointment_date?->format('d M Y');
    $consultationTime = $appointment?->appointment_time ? \Illuminate\Support\Str::of($appointment->appointment_time)->substr(0, 5) : null;
    $patientAgeGender = trim(collect([
        filled($patientProfile?->age) ? $patientProfile->age.' years' : null,
        $patientProfile?->gender ? ucfirst($patientProfile->gender) : null,
    ])->filter()->join(' / '));
@endphp

@once
    @push('styles')
        <style>
            .prescription-document {
                min-width: 960px;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
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
                    background: #ffffff !important;
                    color: #0f172a !important;
                    box-shadow: none !important;
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }

                .prescription-document * {
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }

                .prescription-brand {
                    background: #020617 !important;
                    color: #ffffff !important;
                }

                .prescription-header,
                .prescription-profile-grid,
                .prescription-footer {
                    break-inside: avoid;
                }

                .prescription-profile-grid {
                    display: grid !important;
                    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                }

                .prescription-doctor-panel {
                    border-right: 1px solid #e2e8f0 !important;
                    border-bottom: 0 !important;
                }

                .prescription-table {
                    min-width: 0 !important;
                }
            }
        </style>
    @endpush
@endonce

<article class="prescription-document print-area overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="prescription-brand border-b border-slate-200 bg-slate-950 px-5 py-4 text-white">
        <div class="prescription-header flex flex-row items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <x-logo size="42" :showText="false" class="shrink-0 rounded-lg bg-white/95 p-1 print:border print:border-slate-200" />
                <div>
                    <h1 class="text-xl font-bold tracking-tight">{{ $appName }}</h1>
                    <p class="mt-1 max-w-2xl text-xs leading-5 text-slate-300">Intelligent Care. Human Touch.</p>
                </div>
            </div>

            <div class="rounded-lg border border-white/15 bg-white/10 px-3 py-2 text-right">
                <p class="text-base font-bold">{{ $reference }}</p>
                <p class="mt-0.5 text-xs text-slate-300">{{ $issuedAt }}</p>
            </div>
        </div>
    </div>

    <div class="prescription-profile-grid grid grid-cols-2 border-b border-slate-200 dark:border-slate-800">
        <section class="prescription-doctor-panel border-r border-slate-200 px-5 py-3 dark:border-slate-800">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ $doctor?->name ? 'Dr. '.$doctor->name : '' }}</h2>
            <div class="mt-2 space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p class="">{{ $doctorProfile?->specialization }}{{ filled($doctorProfile?->qualification) ? ', '.$doctorProfile?->qualification : ''}}</p>
                <p class="">{{ filled($doctorProfile?->experience_years) ? $doctorProfile->experience_years.' years' : '' }}</p>
                <p class=" break-all">{{ $doctor?->email }}</p>
            </div>
        </section>

        <section class="px-5 py-3">
            <h2 class="text-lg font-bold text-slate-950 dark:text-white">{{ $patient?->name }}</h2>
            <div class="mt-2 space-y-1 text-sm text-slate-700 dark:text-slate-200">
                <p class="">{{ $patientAgeGender }}</p>
                <p class=" break-all">{{ $patient?->email }}</p>
            </div>
        </section>
    </div>

    <div class="px-5 py-3">
        <section class="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
            <p class="min-h-6 text-base font-semibold leading-6 text-slate-950 dark:text-white">{{ $prescription->diagnosis }}</p>
            @if($appointment?->symptoms)
                <p class="mt-2 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ $appointment->symptoms }}</p>
            @endif
            @if($appointment?->advice)
                <p class="mt-1 text-sm leading-5 text-slate-600 dark:text-slate-300">{{ $appointment->advice }}</p>
            @endif
        </section>

        <section class="mt-4">
            <div class="mb-3 flex items-center justify-between gap-3">
                <h3 class="text-base font-bold text-slate-950 dark:text-white">Medication Plan</h3>
                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-900">{{ $prescription->items->count() }} item{{ $prescription->items->count() === 1 ? '' : 's' }}</span>
            </div>
            <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-800">
                <table class="prescription-table w-full min-w-[760px] border-collapse text-left text-sm">
                    <thead class="bg-slate-100 text-xs uppercase tracking-wider text-slate-600 dark:bg-slate-950 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-3">Medicine</th>
                            <th class="px-4 py-3">Dosage</th>
                            <th class="px-4 py-3">Duration</th>
                            <th class="px-4 py-3">Instructions</th>
                            @if($showCartActions)
                                <th class="no-print px-4 py-3 text-right">Order</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @forelse($prescription->items as $item)
                            <tr class="align-top">
                                <td class="px-4 py-3 font-semibold text-slate-950 dark:text-white">{{ $item->medicine?->name }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->dosage }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->duration }}</td>
                                <td class="px-4 py-3 text-slate-700 dark:text-slate-200">{{ $item->instructions }}</td>
                                @if($showCartActions)
                                    <td class="no-print px-4 py-3 text-right">
                                        @if($item->medicine_id)
                                            <form method="POST" action="{{ route('patient.cart.add') }}" class="inline-flex">
                                                @csrf
                                                <input type="hidden" name="medicine_id" value="{{ $item->medicine_id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button class="rounded-lg bg-blue-600 px-3 py-2 text-xs font-semibold text-white transition hover:bg-blue-700" type="submit">Add to Cart</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400"></span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $showCartActions ? 5 : 4 }}" class="px-4 py-8 text-center text-slate-500"></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if($prescription->notes)
            <section class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm dark:border-amber-900/70 dark:bg-amber-950/30">
                <p class="leading-5 text-slate-800 dark:text-slate-100">{{ $prescription->notes }}</p>
            </section>
        @endif

        <div class="prescription-footer mt-6 grid grid-cols-[1fr_16rem] gap-5 border-t border-slate-200 pt-5 text-sm dark:border-slate-800">
            <p class="leading-5 text-slate-500 dark:text-slate-400">Take medicines exactly as prescribed. Seek urgent care for severe allergic reaction, breathing difficulty, chest pain, or worsening symptoms.</p>
            <div class="text-right">
                <div class="ml-auto h-10 w-44 border-b border-slate-300"></div>
                <p class="mt-2 font-semibold text-slate-950 dark:text-white">{{ $doctor?->name ? 'Dr. '.$doctor->name : '' }}</p>
            </div>
        </div>
    </div>
</article>
