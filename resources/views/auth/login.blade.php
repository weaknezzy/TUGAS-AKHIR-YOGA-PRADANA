<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.ico') }}">
</head>

<body>
    @include('template.navbarMainMenu')

    <main>
        @yield('content')
    </main>

    @include('template.footer') <!-- Footer yang telah Anda buat -->

</body>

</html>
