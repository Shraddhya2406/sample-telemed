@extends('doctor.layout')

@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')
<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Patient Management</h2>
            <p class="mt-0.5 text-xs text-slate-500">Patients you have consulted, with fast access to medical history.</p>
        </div>
        <label class="relative block">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
            <input type="search" data-patient-search placeholder="Search patients" class="h-10 w-full rounded-lg border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-100 sm:w-64">
        </label>
    </div>

    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
        @forelse($patients as $patient)
            <article class="rounded-xl border border-slate-200 bg-white p-3 shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md" data-patient-card>
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-sm font-bold text-emerald-700">{{ str($patient->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-sm font-semibold text-slate-950">{{ $patient->name }}</h3>
                        <p class="truncate text-xs text-slate-500">{{ $patient->email }}</p>
                    </div>
                    <span class="shrink-0 rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold text-slate-500">#{{ $patient->id }}</span>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <div class="rounded-lg bg-slate-50 px-3 py-2">
                        <p class="text-[11px] font-medium text-slate-500">Consultations</p>
                        <p class="text-lg font-semibold leading-6 text-slate-950">{{ $patient->appointments_count }}</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-3 py-2">
                        <p class="text-[11px] font-medium text-emerald-700">Care Status</p>
                        <p class="text-sm font-semibold leading-6 text-emerald-800">Active</p>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-end">
                    <a href="{{ route('doctor.patients.show', $patient) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700">
                        Open History
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <x-doctor.empty-state title="No assigned patients yet" message="Patients will appear after appointments are booked with you." icon="users" />
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $patients->links() }}
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('[data-patient-search]');
        const cards = document.querySelectorAll('[data-patient-card]');
        input?.addEventListener('input', function () {
            const term = input.value.trim().toLowerCase();
            cards.forEach((card) => {
                card.classList.toggle('hidden', term && !card.textContent.toLowerCase().includes(term));
            });
        });
    });
</script>
@endpush
