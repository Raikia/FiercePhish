@if (session('warn'))
    <div class="alertSlide alert alert-warning" style="display: none; ">
        {{ session('warn') }}
    </div>
@endif

@if (isset($warn))
    <div class="alertSlide alert alert-warning" style="display: none; ">
        {{ $warn }}
    </div>
@endif