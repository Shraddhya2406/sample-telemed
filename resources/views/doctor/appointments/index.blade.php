@extends('doctor.layout')

@section('title', 'Appointments')
@section('page-title', 'Appointments')

@section('content')
@php
    $filters = ['' => 'All', 'pending' => 'Pending', 'approved' => 'Confirmed', 'completed' => 'Completed', 'rejected' => 'Rejected'];
@endphp

<section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-base font-semibold text-slate-950">Appointment Management</h2>
            <p class="mt-0.5 text-sm text-slate-500">Search, filter, approve, and launch consultations.</p>
        </div>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <label class="relative block">
                <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                <input type="search" data-table-search placeholder="Search appointments" class="h-10 w-full rounded-xl border border-slate-200 bg-white pl-9 pr-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100 sm:w-64">
            </label>
        </div>
    </div>

    <div class="mt-4 flex gap-2 overflow-x-auto pb-1">
        @foreach($filters as $value => $label)
            <a href="{{ route('doctor.appointments.index', $value ? ['status' => $value] : []) }}" class="whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-semibold transition {{ $status === $value || (!$status && $value === '') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/20' : 'border border-slate-200 bg-white text-slate-600 hover:border-blue-200 hover:text-blue-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-200">
        <table class="min-w-full divide-y divide-slate-200" data-searchable-table>
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Patient</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Schedule</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Notes</th>
                    <th class="px-4 py-2.5 text-right text-xs font-semibold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($appointments as $appointment)
                    <tr class="transition hover:bg-slate-50" data-search-row>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-sm font-semibold text-blue-700">{{ str($appointment->patient->name)->substr(0, 1)->upper() }}</span>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-950">{{ $appointment->patient->name }}</p>
                                    <p class="truncate text-xs text-slate-500">{{ $appointment->patient->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <p class="text-sm font-semibold text-slate-800">{{ $appointment->appointment_date?->format('d M Y') }}</p>
                            <p class="text-xs text-slate-500">{{ substr($appointment->appointment_time, 0, 5) }}</p>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3"><x-doctor.status-badge :status="$appointment->status" /></td>
                        <td class="max-w-xs px-4 py-3 text-sm text-slate-500">{{ str($appointment->notes ?: $appointment->symptoms ?: 'No notes provided')->limit(56) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex flex-nowrap justify-end gap-2">
                                @if($appointment->status === 'approved')
                                    <a href="{{ route('doctor.call.start', ['patient' => $appointment->patient, 'appointment_id' => $appointment->id]) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-500 px-2.5 py-1.5 text-xs font-semibold text-white transition hover:bg-emerald-600">
                                        <i data-lucide="video" class="h-4 w-4"></i>
                                        Start Call
                                    </a>
                                @endif
                                @if($appointment->status === 'completed' && $appointment->prescription)
                                    <a href="{{ route('doctor.prescriptions.show', $appointment->prescription) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-blue-200 px-2.5 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-50">Prescription</a>
                                @endif
                                <a href="{{ route('doctor.appointments.show', $appointment) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-2.5 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-blue-200 hover:text-blue-700">
                                    Details
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6">
                            <x-doctor.empty-state title="No appointments found" message="Try another status filter or wait for new patient bookings." icon="calendar-x" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $appointments->links() }}
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.querySelector('[data-table-search]');
        const rows = document.querySelectorAll('[data-search-row]');
        input?.addEventListener('input', function () {
            const term = input.value.trim().toLowerCase();
            rows.forEach((row) => {
                row.classList.toggle('hidden', term && !row.textContent.toLowerCase().includes(term));
            });
        });
    });
</script>
@endpush
