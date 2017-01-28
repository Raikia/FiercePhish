@if (isset($warn) || session('warn') || isset($success) || session('success') || count($errors) > 0 || $latest_fiercephish_version != $current_fiercephish_version)
    <div id="notificationsArea" style="margin-top: 67px; width: 500px; margin-left: auto; margin-right: auto;">
        @if ($latest_fiercephish_version != $current_fiercephish_version)
            <div class="alert alert-warning" style="color: #444346;">
                New FiercePhish update available (v{{ $latest_fiercephish_version }})!  Run "./update.sh" on the server to update to the latest version! (located in "{{ base_path('update.sh') }}")
            </div>
        @endif
        
        @include('common.errors')
        @include('common.warnings')
        @include('common.successes')
    </div>
@endif