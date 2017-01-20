@extends('layouts.login', ['title' => 'Validate 2FA'])

@section('content')

<div class="logo">
    <img src="{{ asset('images/fiercephish_logo.png') }}" alt="FiercePhish" />
</div>
<div class="login-page">
  <div class="form">
     @if (count($errors) > 0)
         @foreach ($errors->all() as $error)
             <div class="error">{{ $error }}</div>
         @endforeach
     @endif
    <input type="text" id="passcode" placeholder="Passcode" data-inputmask="'mask' : '999 999'" style="letter-spacing: 8px; font-size: 18pt; text-align: center; font-family: 'Courier New'" autofocus />
    <form class="login-form" id="actualValidateForm" method="post" action="{{ action('Auth\LoginController@postValidateToken') }}">
        {{ csrf_field() }}
        <input type="hidden" name="totp" id="totp" value="" />
        <input type="submit" class="button" value="Validate Code" />
    </form>
  </div>
</div>
@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    $("#actualValidateForm").submit(function(e) {
        $("#totp").val($("#passcode").val().replace(" ",""));
        return true;
    });
    
    $('#passcode').keyup(function () {
        if (this.value.indexOf('_') == -1) {
            $('#actualValidateForm').submit();
        }
    });
    $(document).ready(function() {
        $("#passcode").val('');
    });
</script>
@endsection
