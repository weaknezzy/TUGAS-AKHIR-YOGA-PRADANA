<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register</title>
</head>

<body>
    @include('template.navbarMainMenu')

    <main>
        @yield('content')
    </main>

    @include('template.footer')

</body>

</html>
