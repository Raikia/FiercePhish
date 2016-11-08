@if (isset($warn) || session('warn') || isset($success) || session('success') || count($errors) > 0)
    <div id="notificationsArea" style="margin-top: 67px; width: 500px; margin-left: auto; margin-right: auto;">
        @include('common.errors')
        @include('common.warnings')
        @include('common.successes')
    </div>
@endif