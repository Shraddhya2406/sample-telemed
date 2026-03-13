@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Health Assessment Quiz</h2>
    <p class="text-gray-600 mb-6">This quiz provides medicine recommendations, not a diagnosis.</p>
    <form method="GET" action="{{ route('patient.health-quiz.index') }}">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Start Quiz</button>
    </form>
</div>
@endsection