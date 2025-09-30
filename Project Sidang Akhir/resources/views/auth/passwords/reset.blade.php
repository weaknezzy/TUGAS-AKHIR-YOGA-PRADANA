@extends('template.loginForm')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Reset Password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Masukkan email dan password baru Anda
            </p>
        </div>
        @if (session('status'))
            <div class="mb-4 text-green-700 bg-green-100 border border-green-300 rounded-lg px-4 py-3 text-center">
                {{ session('status') }}
            </div>
        @endif
        <form class="mt-8" method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="space-y-5">
                <div>
                    <label for="email" class="sr-only">Alamat Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                            <i class="fa-solid fa-envelope text-black"></i>
                        </div>
                        <input id="email" name="email" type="email" required autofocus value="{{ old('email') }}"
                            class="appearance-none rounded-lg relative block w-full pl-10 pr-8 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('email') border-red-500 @enderror"
                            placeholder="Masukkan Email">
                    </div>
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="sr-only">Password Baru</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                            <i class="fa-solid fa-lock text-black"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-lg relative block w-full pl-10 pr-10 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('password') border-red-500 @enderror"
                            placeholder="Masukkan Password Baru">
                        <button type="button" onclick="togglePassword('password', this)" tabindex="-1"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 focus:outline-none z-20">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password-confirm" class="sr-only">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-20">
                            <i class="fa-solid fa-lock text-black"></i>
                        </div>
                        <input id="password-confirm" name="password_confirmation" type="password" required
                            class="appearance-none rounded-lg relative block w-full pl-10 pr-10 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            placeholder="Konfirmasi Password Baru">
                        <button type="button" onclick="togglePassword('password-confirm', this)" tabindex="-1"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 focus:outline-none z-20">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="mt-8">
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fa-solid fa-key text-black"></i>
                    </span>
                    Reset Password
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection 