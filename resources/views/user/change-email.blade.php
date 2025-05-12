@extends('layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="bg-white p-8 rounded-2xl shadow-lg w-full max-w-md text-center">
        @if (session('status'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('status') }}
            </div>
        @endif

        <h2 class="text-2xl font-bold mb-6">Email cím megváltoztatása</h2>

        <form method="POST" action="{{ route('email.update') }}">
            @csrf

            <div class="mb-4">
                <label for="current_email" class="block text-sm font-medium text-gray-700 text-center">Jelenlegi email</label>
                <input id="current_email" type="email" name="current_email" required class="mt-1 mx-auto block w-3/4 rounded-lg border-gray-300 shadow-sm text-center">
            </div>

            <div class="mb-6">
                <label for="new_email" class="block text-sm font-medium text-gray-700 text-center">Új email</label>
                <input id="new_email" type="email" name="new_email" required class="mt-1 mx-auto block w-3/4 rounded-lg border-gray-300 shadow-sm text-center">
            </div>

            <div class="flex justify-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-black font-semibold py-2 px-4 rounded-lg">
                    Email frissítése
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
