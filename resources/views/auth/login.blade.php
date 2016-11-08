@extends('layouts.login')

@section('content')

<div class="logo">
    <img src="images/login_logo.png" style="width: 100px;" />
</div>
<div class="login-page">
  <div class="form">
     @if (count($errors) > 0)
         @foreach ($errors->all() as $error)
             <div class="error">{{ $error }}</div>
         @endforeach
     @endif
    <form class="login-form" method="post" action="{{ url('login') }}">
        {{ csrf_field() }}
      <input type="text" name="name" placeholder="Username" autofocus />
      <input type="password" name="password" placeholder="Password"/>
      <input type="submit" class="button" value="Login" />
    </form>
  </div>
</div>
@endsection
