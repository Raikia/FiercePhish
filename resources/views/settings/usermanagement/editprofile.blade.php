@extends('layouts.app')

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Edit Profile</h3>
  </div>
<!--
  <div class="title_right">
    <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
      <div class="input-group">
        <input type="text" class="form-control" placeholder="Search for...">
        <span class="input-group-btn">
          <button class="btn btn-default" type="button">Go!</button>
        </span>
      </div>
    </div>
  </div>-->
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel"><!--
      <div class="x_title">
        <h2>Plain Page</h2>
        <ul class="nav navbar-right panel_toolbox">
          <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
          </li>
          <li class="dropdown">
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
            <ul class="dropdown-menu" role="menu">
              <li><a href="#">Settings 1</a>
              </li>
              <li><a href="#">Settings 2</a>
              </li>
            </ul>
          </li>
          <li><a class="close-link"><i class="fa fa-close"></i></a>
          </li>
        </ul>
        <div class="clearfix"></div>
      </div>-->
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
</div>


@endsection