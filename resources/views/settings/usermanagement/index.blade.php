@extends('layouts.app', ['title' => 'User Management'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>User Management</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel" style="overflow: auto;">
      <div class="x_content">
          <table class="table table-striped table-bordered datatable">
              <thead>
                  <tr>
                      <th>Username</th>
                      <th>Email</th>
                      <th>Phone Number</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                  @if (count($users) > 0)
                      @foreach ($users as $user)
                          <tr>
                              <td><a href="{{ action('SettingsController@get_editprofile', ['id' => $user->id ]) }}">{{ str_limit($user->name,50) }}</a></td>
                              <td>{{ str_limit($user->email,50) }}</td>
                              <td>{{ str_limit($user->phone_number,50) }}</td>
                              <td>{{ \App\Libraries\DateHelper::readable($user->created_at) }}</td>
                          </tr>
                      @endforeach
                  @else
                      <tr>
                          <td colspan="4" style="text-align: center;">No Users Yet</td>
                      </tr>
                  @endif
              </tbody>
          </table>
      </div>
    </div>
  </div>
</div>


<!----------------- -->

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-plus"></i> Add a User</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@addUser') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Username <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12" value="{{ old('name') }}">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="password">Password <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="password" id="password" name="password" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Email <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="email" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12" value="{{ old('email') }}">
            </div>
          </div>
          <div class="form-group">
            <label for="phone_number" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Number </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input id="phone_number" class="form-control col-md-7 col-xs-12" type="text" name="phone_number" data-inputmask="'mask' : '(999) 999-9999'"  value="{{ old('phone_number') }}">
              <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
            </div>
          </div>

          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="add_user_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Create User</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>


  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-trash"></i> Delete User</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" id="deleteUserForm" method="post" action="{{ action('SettingsController@deleteUser') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="import_file">Select User <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <select class="form-control" style="width: 200px;" name="user">
                        <option></option>
                        @foreach ($users as $user)
                            @if ($user->id != auth()->user()->id)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" class="btn btn-success" data-toggle="modal" data-target=".deleteuser-modal-sm">Delete User</button>
              <div class="modal fade deleteuser-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
                          </button>
                          <h4 class="modal-title" id="myModalLabel2">Are you sure?</h4>
                        </div>
                        <div class="modal-body">
                          <h4>Delete User?</h4>
                          <p>This action cannot be undone!</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                          <button type="button" id="deleteUser_btn" class="btn btn-danger" style="margin-bottom: 5px;">Delete User</button>
                        </div>

                      </div>
                    </div>
                  </div>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    $("#add_user_clear_btn").click(function() {
        $("#name").val('');
        $("#email").val('');
        $("#password").val('');
        $("#phone_number").val('');
    });
    
    $("#deleteUser_btn").click(function() {
        $("#deleteUserForm").submit();
    })
</script>
@endsection