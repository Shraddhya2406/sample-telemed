@extends('layouts.patient')

@section('title', 'Edit Profile')
@section('page_title', 'Edit Profile')
@section('eyebrow', 'Your health account')

@section('content')
@php
    $user = Auth::user();
    $profile = $user->patientProfile;
@endphp

<div class="mx-auto max-w-3xl pb-20 lg:pb-0">
    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-col gap-3 border-b border-slate-100 pb-5 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950 dark:text-white">Profile details</h2>
                <p class="mt-1 text-sm text-slate-500">Keep your account and medical summary up to date.</p>
            </div>
            <a href="{{ route('patient.profile') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
        </div>

        <form method="POST" action="{{ route('patient.profile.update') }}" class="mt-6 space-y-5">
            @csrf
            @method('PUT')

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="text-sm font-bold text-slate-700 dark:text-slate-200">Full name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-blue-950">
                    @error('name') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="text-sm font-bold text-slate-700 dark:text-slate-200">Email address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-blue-950">
                    @error('email') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="age" class="text-sm font-bold text-slate-700 dark:text-slate-200">Age</label>
                    <input id="age" name="age" type="number" min="1" max="120" value="{{ old('age', $profile?->age) }}" required class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-blue-950">
                    @error('age') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="gender" class="text-sm font-bold text-slate-700 dark:text-slate-200">Gender</label>
                    <select id="gender" name="gender" required class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-blue-950">
                        <option value="">Select gender</option>
                        <option value="male" @selected(old('gender', $profile?->gender) === 'male')>Male</option>
                        <option value="female" @selected(old('gender', $profile?->gender) === 'female')>Female</option>
                        <option value="other" @selected(old('gender', $profile?->gender) === 'other')>Other</option>
                    </select>
                    @error('gender') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="medical_history" class="text-sm font-bold text-slate-700 dark:text-slate-200">Medical history</label>
                <textarea id="medical_history" name="medical_history" rows="6" maxlength="5000" class="mt-2 w-full rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100 dark:border-slate-700 dark:bg-slate-950 dark:focus:ring-blue-950" placeholder="Allergies, chronic conditions, current medications, past surgeries, or other notes for your care team.">{{ old('medical_history', $profile?->medical_history) }}</textarea>
                @error('medical_history') <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 dark:border-slate-800 sm:flex-row sm:justify-end">
                <a href="{{ route('patient.profile') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-sm shadow-blue-600/20 transition hover:bg-blue-700">Save changes</button>
            </div>
        </form>
    </section>
</div>
@endsection
