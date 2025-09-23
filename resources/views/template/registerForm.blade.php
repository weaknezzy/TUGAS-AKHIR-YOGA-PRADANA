@extends('auth.register')

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.tailwindcss.com"></script>

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-2xl">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Daftar Akun Baru
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Silakan lengkapi data diri Anda
            </p>
        </div>

        @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('register') }}" method="POST">
            @csrf
            <input type="hidden" name="role" value="pelanggan">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="name" class="sr-only">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input id="name" name="name" type="text" required value="{{ old('name') }}"
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('name') border-red-500 @enderror"
                            placeholder="Masukkan Nama Lengkap">
                    </div>
                </div>

                <div>
                    <label for="email" class="sr-only">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400"></i>
                        </div>
                        <input id="email" name="email" type="email" required value="{{ old('email') }}"
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('email') border-red-500 @enderror"
                            placeholder="Masukkan Email">
                        <div id="email-status" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <!-- Icon status akan muncul di sini -->
                        </div>
                    </div>
                    <div id="email-message" class="mt-1 text-sm"></div>
                </div>

                <div>
                    <label for="no_telp" class="sr-only">Nomor Telepon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <input id="no_telp" name="no_telp" type="tel" required value="{{ old('no_telp') }}"
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('no_telp') border-red-500 @enderror"
                            placeholder="Masukkan Nomor Telepon (min. 10 digit)">
                    </div>
                </div>

                <div>
                    <label for="address" class="sr-only">Alamat</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-home text-gray-400"></i>
                        </div>
                        <textarea id="address" name="address" required
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('address') border-red-500 @enderror"
                            placeholder="Masukkan Alamat Lengkap (min. 10 karakter)" rows="3">{{ old('address') }}</textarea>
                    </div>
                </div>

                <div>
                    <label for="password" class="sr-only">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password" name="password" type="password" required
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm @error('password') border-red-500 @enderror"
                            placeholder="Masukkan Password (min. 8 karakter)">
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="sr-only">Konfirmasi Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" required
                            class="appearance-none rounded-lg relative block w-full px-12 py-3 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
                            placeholder="Konfirmasi Password">
                    </div>
                </div>
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-user-plus text-indigo-500 group-hover:text-indigo-400"></i>
                    </span>
                    Daftar Sekarang
                </button>
            </div>

            <div class="text-center mt-4">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        Login disini
                    </a>
                </p>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('email');
    const emailStatus = document.getElementById('email-status');
    const emailMessage = document.getElementById('email-message');
    let emailTimeout;

    emailInput.addEventListener('input', function() {
        const email = this.value.trim();
        
        // Clear previous timeout
        clearTimeout(emailTimeout);
        
        // Clear previous status
        emailStatus.innerHTML = '';
        emailMessage.innerHTML = '';
        emailMessage.className = 'mt-1 text-sm';
        
        // Reset border color
        emailInput.classList.remove('border-red-500', 'border-green-500');
        emailInput.classList.add('border-gray-300');
        
        // If email is empty, don't validate
        if (email === '') {
            return;
        }
        
        // Show loading indicator
        emailStatus.innerHTML = '<i class="fas fa-spinner fa-spin text-gray-400"></i>';
        
        // Debounce the validation (wait 500ms after user stops typing)
        emailTimeout = setTimeout(function() {
            // Validate email format first
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                emailStatus.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
                emailMessage.innerHTML = 'Format email tidak valid';
                emailMessage.className = 'mt-1 text-sm text-red-600';
                emailInput.classList.remove('border-gray-300');
                emailInput.classList.add('border-red-500');
                return;
            }
            
            // Send AJAX request to check email
            fetch('{{ route("check.email") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    emailStatus.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                    emailMessage.innerHTML = data.message;
                    emailMessage.className = 'mt-1 text-sm text-green-600';
                    emailInput.classList.remove('border-gray-300', 'border-red-500');
                    emailInput.classList.add('border-green-500');
                } else {
                    emailStatus.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                    emailMessage.innerHTML = data.message;
                    emailMessage.className = 'mt-1 text-sm text-red-600';
                    emailInput.classList.remove('border-gray-300', 'border-green-500');
                    emailInput.classList.add('border-red-500');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                emailStatus.innerHTML = '<i class="fas fa-exclamation-circle text-red-500"></i>';
                emailMessage.innerHTML = 'Terjadi kesalahan saat memvalidasi email';
                emailMessage.className = 'mt-1 text-sm text-red-600';
                emailInput.classList.remove('border-gray-300', 'border-green-500');
                emailInput.classList.add('border-red-500');
            });
        }, 500);
    });
});
</script>
@endsection