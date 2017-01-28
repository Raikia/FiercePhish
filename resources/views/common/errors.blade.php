@if (count($errors) > 0)
    <div class="alertSlide alert alert-danger" style="display: none; ">
        <strong>Whoops! Something went wrong!</strong>
        <br /><br />
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif