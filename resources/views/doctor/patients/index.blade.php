@extends('doctor.layout')

@section('title', 'Patients')
@section('page-title', 'Patients')

@section('content')
<section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-950">Patient Management</h2>
            <p class="mt-1 text-sm text-slate-500">A compact view of patients you have consulted, with fast access to medical history.</p>
        </div>
        <label class="relative block">
            <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
            <input type="search" data-patient-search placeholder="Search patients" class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-72">
        </label>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($patients as $patient)
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-lg" data-patient-card>
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-base font-bold text-blue-700">{{ str($patient->name)->substr(0, 1)->upper() }}</span>
                    <div class="min-w-0 flex-1">
                        <h3 class="truncate text-base font-semibold text-slate-950">{{ $patient->name }}</h3>
                        <p class="truncate text-sm text-slate-500">{{ $patient->email }}</p>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-slate-50 p-3">
                        <p class="text-xs font-medium text-slate-500">Consultations</p>
                        <p class="mt-1 text-xl font-semibold text-slate-950">{{ $patient->appointments_count }}</p>
                    </div>
                    <div class="rounded-2xl bg-emerald-50 p-3">
                        <p class="text-xs font-medium text-emerald-700">Care Status</p>
                        <p class="mt-1 text-sm font-semibold text-emerald-800">Active</p>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                    <a href="{{ route('doctor.patients.show', $patient) }}" class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">
                        Open History
                        <i data-lucide="arrow-right" class="h-4 w-4"></i>
                    </a>
                    <span class="text-xs font-medium text-slate-400">ID #{{ $patient->id }}</span>
                </div>
            </article>
        @empty
            <div class="sm:col-span-2 xl:col-span-3">
                <x-doctor.empty-state title="No assigned patients yet" message="Patients will appear after appointments are booked with you." icon="users" />
            </div>
        @endforelse
    </div>

    <div class="mt-6">
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
