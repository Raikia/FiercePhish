@extends('layouts.app')

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Application Configuration Settings</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div class="x_panel">
            <div class="x_content">
                <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@post_editprofile') }}">
                </form>
            </div>
        </div>
    </div>
</div>

@endsection