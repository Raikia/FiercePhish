@extends('layouts.app')

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>List of Targets</h3>
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
          <table id="datatable" class="table table-striped table-bordered datatable">
              <thead>
                  <tr>
                      <th>First name</th>
                      <th>Last name</th>
                      <th>Email</th>
                      <th>List Membership</th>
                  </tr>
              </thead>
              <tbody>
                  @if (count($targetUsers) > 0)
                      @foreach ($targetUsers as $user)
                          <tr>
                              <td>{{ $user->first_name }}</td>
                              <td>{{ $user->last_name }}</td>
                              <td>{{ $user->email }}</td>
                              <td>
                                  @if (count($user->lists) > 0)
                                      <ul>
                                          @foreach ($user->lists as $l)
                                              <li>{{ $l->name }}</li>
                                          @endforeach
                                      </ul>
                                  @else
                                      N/A
                                  @endif
                              </td>
                          </tr>
                      @endforeach
                  @else
                      <tr>
                          <td colspan="4" style="text-align: center;">No Targets Yet</td>
                      </tr>
                  @endif
              </tbody>
          </table>
      </div>
    </div>
  </div>
</div>


@endsection