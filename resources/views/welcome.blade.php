<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bookstore</title>
    <script src="{{ asset('js/app.js')}}"></script>
    <link rel="stylesheet" href="{{asset("/css/app.css")}}"/>
    <script>
        window.CSRF_TOKEN = '{{ csrf_token() }}';
    </script>
</head>
<body class="antialiased">
<div id="app">
    <front></front>
</div>
</body>
</html>
