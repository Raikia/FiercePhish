@extends('layouts.app', ['title' => 'Edit Profile'])

@section('content')


<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
          <h2>Edit Profile</h2>
          <div class="clearfix"></div>
        </div>
      <div class="x_content">
          <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@post_editprofile') }}">
            {{ csrf_field() }}
            <input type="hidden" name="user_id" value="{{ $user->id }}" />
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Username <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="{{ old('name', $user->name) }}">
              </div>
            </div>
            <div class="form-group">
              <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12" value="{{ old('email', $user->email) }}">
              </div>
            </div>
            <div class="form-group">
              <label for="phone_number" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Number</label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input id="phone_number" data-inputmask="'mask' : '(999) 999-9999'" class="form-control col-md-7 col-xs-12" type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}">
                <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
              </div>
            </div>
            <div class="form-group">
              <label for="password" class="control-label col-md-3 col-sm-3 col-xs-12">New Password <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input id="password" name="password" class="form-control col-md-7 col-xs-12" type="text">
              </div>
            </div>
            <div class="form-group">
              <label for="password_confirmation" class="control-label col-md-3 col-sm-3 col-xs-12">New Password Confirm <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input id="password_confirmation" name="password_confirmation" class="form-control col-md-7 col-xs-12" type="text">
              </div>
            </div>
            @if (auth()->user()->id == $user->id)
            <div class="form-group" style="margin-top: 70px;">
              <label for="current_password" class="control-label col-md-3 col-sm-3 col-xs-12">Current Password <span class="required">*</span>
              </label>
              <div class="col-md-6 col-sm-6 col-xs-12">
                <input id="current_password" name="current_password" class="form-control col-md-7 col-xs-12" required="required" type="password">
              </div>
            </div>
            @endif
            <div class="ln_solid"></div>
            <div class="form-group">
              <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                @if ($self != '')
                  <a href="{{ action('SettingsController@index') }}" class="btn btn-primary">Back</a>
                  <input type="hidden" name="type" value="diff" />
                @else
                  <input type="hidden" name="type" value="self" />
                @endif
                <button type="submit" class="btn btn-success">Save Profile</button>
              </div>
            </div>
          </form>
      </div>
    </div>
  </div>
  @if (auth()->user()->id == $user->id)
    <div class="col-md-6 col-sm-6 col-xs-12">
      <div class="x_panel">
        <div class="x_title">
          <h2>Google 2FA Authentication</h2>
          <div class="clearfix"></div>
        </div>
        <div class="x_content">
          <form id="enable2faform" class="form-horizontal form-label-left" method="post" action="{{ action('Google2FAController@enableTwoFactor') }}">
            {{ csrf_field() }}
            <div class="form-group">
              <div style="text-align: center;">
                @if (auth()->user()->google2fa_secret == null)
                  <button class="btn btn-primary">Enable Google 2FA Authentication</button>
                @else
                  Open up your 2FA mobile app and scan the following QR barcode:
                  <br />
                  <img alt="Image of QR barcode" src="{{ $fa_image }}" />
                  
                  <br />
                  If your 2FA mobile app does not support QR barcodes, 
                  enter in the following number: <code>{{ $fa_secret }}</code>
                  <br /><br />
                  <a id="dis2fa" href="{{ action('Google2FAController@disableTwoFactor') }}" class="btn btn-danger">Disable 2FA</a>
                  <button id="regen2fa" class="btn btn-primary">Generate New 2FA Code</a>
                @endif
              </div>
            </div>
          </form>
          <form id="disable2faform" class="form-horizontal form-label-left" method="post" action="{{ action('Google2FAController@disableTwoFactor') }}">
            {{ csrf_field() }}
          </form>
        </div>
      </div>
    </div>
  @endif
</div>

@endsection

@section('footer')

<script type="text/javascript">
  /* global $ */
  /* global bootbox */
  $("#regen2fa").click(function(e) {
        var currentForm = this;
        e.preventDefault();
        bootbox.confirm("Are you sure you want to recreate the 2FA code?  You will have to import the token again", function(result) {
            if (result) {
                $("#enable2faform").submit();
            }
        });
    });
  $("#dis2fa").click(function(e) {
    var currentForm = this;
        e.preventDefault();
        bootbox.confirm("Are you sure you want to disable 2FA?", function(result) {
            if (result) {
                $("#disable2faform").submit();
            }
        });
  });
  
  CURRENT_URL = "{{ action('SettingsController@index') }}";
  
  
  $(document).ready(function() {
    $("#phone_number").inputmask();
  });
</script>

@endsection