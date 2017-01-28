<!DOCTYPE html>
<html class=''>
  <head>
    <meta charset='UTF-8'>
    <meta name="robots" content="noindex">
    <link href="{{ asset('css/login_page.css') }}" rel="stylesheet">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <title>{{ config('fiercephish.APP_NAME') }} &raquo; {{ $title }}</title>
  </head>
  <body>
    @yield('content')
    <script src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery.inputmask/dist/jquery.inputmask.bundle.js') }}"></script>
    @if (config('fiercephish.ANALYTICS') === true)
    <script src="{{ asset('js/ta.js') }}"></script>
    @endif
    @yield('footer')
    <script type="text/javascript">
      /* global $ */
      $(":input").inputmask();
    </script>
  </body>
</html>