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
    <form class="login-form" id="validateForm">
      <input type="text" id="passcode" placeholder="Passcode" data-inputmask="'mask' : '999 999'" style="letter-spacing: 8px; font-size: 18pt; text-align: center; font-family: 'Courier New'" autofocus />
      <input type="submit" class="button" value="Validate Code" />
    </form>
    <form class="login-form" id="actualValidateForm" method="post" action="{{ action('Auth\AuthController@postValidateToken') }}">
        {{ csrf_field() }}
        <input type="hidden" name="totp" id="totp" value="" />
    </form>
  </div>
</div>
@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    $("#validateForm").submit(function(e) {
        e.preventDefault();
        $("#totp").val($("#passcode").val().replace(" ",""));
        $("#actualValidateForm").submit();
    });
</script>
@endsection
