<!doctype html>
<html dir="rtl"><head><meta charset="utf-8"><title>@yield('title')</title></head>
<body>
{{-- Same shape as the real Admin.master: the content sits inside <main id="app">, the js and scripts sections after it. --}}
<main class="main-content" id="app">@yield('content')</main>
@yield('js')
@yield('scripts')
</body></html>
