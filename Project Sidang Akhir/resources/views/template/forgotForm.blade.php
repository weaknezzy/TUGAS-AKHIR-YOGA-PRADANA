@extends('auth.forgot')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Lupa Kata Sandi?
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Masukkan email Anda untuk mereset kata sandi
            </p>
        </div>

        @if (session('status'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('status') }}</span>
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST">
            @csrf
            <div>
                <label for="email" class="sr-only">Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input id="email" name="email" type="email" required
                        class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                        placeholder="Masukkan Email Anda">
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-paper-plane text-indigo-500 group-hover:text-indigo-400"></i>
                    </span>
                    Kirim Link Reset Password
                </button>
            </div>

            <div class="flex items-center justify-center space-x-4 mt-4">
                <a href="{{ route('login') }}" 
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500 flex items-center">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Kembali ke Login
                </a>
                <span class="text-gray-300">|</span>
                <a href="{{ route('register') }}" 
                   class="text-sm font-medium text-indigo-600 hover:text-indigo-500 flex items-center">
                    <i class="fas fa-user-plus mr-2"></i>
                    Daftar Akun Baru
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
