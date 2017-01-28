@if (session('success'))
    <div style="display: none; " class="alertSlide alert alert-success">
        <strong>Success</strong>
        <br /><br />
        {{ session('success') }}
    </div>
@endif

@if (isset($success))
    <div class="alertSlide alert alert-success" style="display: none; ">
        <strong>Success</strong>
        <br /><br />
        {{ $success }}
    </div>
@endif