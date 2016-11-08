@if (session('warn'))
    <div class="alert alert-warning" style="display: none; ">
        <strong>Alert</strong>
        <br /><br />
        {{ session('warn') }}
    </div>
@endif

@if (isset($warn))
    <div class="alert alert-warning" style="display: none; ">
        <strong>Alert</strong>
        <br /><br />
        {{ $warn }}
    </div>
@endif