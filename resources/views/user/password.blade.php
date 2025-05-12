@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow-md text-center">
    <h2 class="text-xl font-semibold mb-4 text-center">Jelszó megváltoztatása</h2>

    @if(session('success'))
        <div class="text-green-600 mb-4 text-center">{{ session('success') }}</div>
    @endif

    <form method="POST"  action="{{ route('password.update') }}">
        @csrf

        <div class="mb-4">
            <label class="block mb-1 text-center">Jelenlegi jelszó</label>
            <input type="password" name="current_password" class="w-full border rounded px-3 py-2 text-center" required>
            @error('current_password')
                <div class="text-red-500 text-sm mt-1 text-center">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-center">Új jelszó</label>
            <input type="password" name="password" class="w-full border rounded px-3 py-2 text-center" required>
            @error('password')
                <div class="text-red-500 text-sm mt-1 text-center">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1 text-center">Új jelszó megerősítése</label>
            <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2 text-center" required>
        </div>

        <button type="submit" class="bg-blue-600 text-blcak px-4 py-2 rounded hover:bg-blue-700 text-center">
            Jelszó frissítése
        </button>
    </form>
</div>
@endsection
