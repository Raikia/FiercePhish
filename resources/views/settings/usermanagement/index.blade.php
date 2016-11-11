@extends('layouts.app')

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
                              <td>{{ $user->created_at }}</td>
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
              <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12">
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
              <input type="email" id="email" name="email" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label for="phone_number" class="control-label col-md-3 col-sm-3 col-xs-12">Phone Number </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input id="phone_number" class="form-control col-md-7 col-xs-12" type="text" name="phone_number" data-inputmask="'mask' : '(999) 999-9999'" >
              <span class="fa fa-phone form-control-feedback right" aria-hidden="true"></span>
            </div>
          </div>

          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="add_target_clear_btn" class="btn btn-primary">Clear</button>
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
        <h2><i class="fa fa-download"></i> Delete User</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" enctype="multipart/form-data" method="post" action="{{ action('TargetsController@importTargets') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="import_file">Import File  <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <span class="input-group-btn">
                        <label class="btn btn-primary"><i class="fa fa-file-o"></i><input type="file" name="import_file" id="import_file" style="visibility: hidden; position:absolute;" /></label>
                    </span>
                    <input type="text" id="selectedFile" class="form-control" readonly />
                </div>
                
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">CSV Format 
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <p style="margin-top: 8px;">First Name, Last Name, Email, [Notes]</p>
                </div>
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="import_file_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Create Target</button>
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
    $("#add_target_clear_btn").click(function() {
        $("#first_name").val('');
        $("#last_name").val('');
        $("#email").val('');
    });
    
    $("#import_file").change(function() {
        $("#selectedFile").val($(this).val());
    });
    
    $("#import_file_clear_btn").click(function() {
        $("#import_file").val('');
        $("#selectedFile").val('');
    });
    
    var dt = $(".datatable").DataTable();
    
    $(".editnotes").editable();
    
    $(".editnotes").on('save', function() {
        setTimeout(function() {
            dt.rows().invalidate();
        }, 500);
    })
</script>
@endsection