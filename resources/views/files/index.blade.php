@extends('layouts.app', ['title' => 'Hosted Files'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted Files</h3>
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
                      <th>Original File Name</th>
                      <th>File Path</th>
                      <th>Mime Type</th>
                      <th>Action</th>
                      <th>Views</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                  @if (count($files) > 0)
                      @foreach ($files as $file)
                          <tr>
                              <td><a href="{{ action('HostedFileController@file_details', ['id' => $file->id ]) }}">{{ str_limit($file->original_file_name,50) }}</a></td>
                              <td><a href="{{ \Request::root().$file->getPathWithVar() }}">{{ str_limit($file->getPathWithVar(),50) }}</a></td>
                              <td>{{ str_limit($file->file_mime,50) }}</td>
                              <td>{{ $file->getAction() }}
                              @if ($file->notify_access)
                                (<i class="fa fa-bell-o"></i>)
                              @endif
                              </td>
                              <td data-order="{{ $file->views()->count() }}">{{ $file->views()->count() }} / 
                              @if ($file->kill_switch != null)
                                {{ $file->kill_switch }}
                              @else 
                                &infin;
                              @endif
                              @if ($file->credentials()->count() > 0)
                                &nbsp;&nbsp;&nbsp;({{ $file->credentials()->count() }} <i class="fa fa-key"></i>)
                              @endif
                              </td>
                              <td>{{ \App\Libraries\DateHelper::readable($file->created_at) }}</td>
                          </tr>
                      @endforeach
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
        <h2><i class="fa fa-plus"></i> Host a file</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" enctype="multipart/form-data" method="post" action="{{ action('HostedFileController@addfile') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12">File Upload <span class="required">*</span></label>
            <div class="col-md-9 col-sm-9 col-xs-12">
              <label class="btn btn-primary" for="attachment">
                <input type="file" required="required" id="attachment" name="attachment" style="display: none;" onchange="$('#upload-file-info').html($(this).val())">
                Browse...
              </label>
              <span class="label label-info" id="upload-file-info"></span>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="email">Action <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <select name="action" class="form-control">
                    @foreach (\App\HostedFile::getActions() as $val => $action)
                        <option value="{{ $val }}">{{ $action }}</option>
                    @endforeach
                </select>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">File Path </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="path" name="path" class="form-control col-md-7 col-xs-12" placeholder="example/location/file.php" value="{{ old('name') }}">
            </div>
          </div>
          <div class="form-group">
            <label for="uid_tracker" class="control-label col-md-3 col-sm-3 col-xs-12">UID Tracker Variable </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input id="uid_tracker" class="form-control col-md-7 col-xs-12 tt" type="text" name="uid_tracker" placeholder="Optional" title="This will allow you to correlate Email access uid hashes to this file">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Selected Path <span class="required">*</span></label>
            <div class="col-md-9 col-sm-9 col-xs-12" style="margin-top:8px;" id="selected_path">
              <span id="currentPath">{{ \Request::root() }}/</span><span id="routeResult" style="color: #FF0000;">
                  @if (\App\HostedFile::path_already_exists('/'))
                   - Taken!
                  @endif
                  </span>
            </div>
          </div>
          <div class="form-group" id="action_on_invalid" style="display: none;">
            <label class="control-label col-md-3 col-sm-3 col-xs-12">Action on invalid tracker</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <select name="invalid_action" class="form-control">
                @foreach (\App\HostedFile::getInvalidActions() as $val => $action)
                  <option value="{{ $val }}">{{ $action }}</option>
                @endforeach
              </select>
            </div>
          </div>
          

          <div class="form-group">
              <label for="phone_number" class="control-label col-md-3 col-sm-3 col-xs-12">Maximum # requests</label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input type="number" name="kill_switch" class="form-control" placeholder="Infinite"/>
                </div>
          </div>
          
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12">Notify on access</label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="checkbox">
                    <label>
                    <input type="checkbox" name="notify" value="1">
                    </label>
                </div>
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="add_file_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Add File</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>


  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-bell-o"></i> User Notification Settings</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div style="text-align: center;">
          <p>Note: If you view the file while logged in to FiercePhish, no alert will be generated and your view will not be logged. To fully test the notification system, you must view the file in "incogneto" mode or while you are logged out of FiercePhish.</p>
        </div>
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Username</th>
              <th>Notification Type</th>
            </tr>
          </thead>
          <tbody>
            @foreach (App\User::all() as $user)
              <tr>
                <td><a href="{{ action('SettingsController@get_editprofile', ['id' => $user->id]) }}">{{ $user->name }}</a></td>
                <td>{{ App\User::getNotifications()[$user->notify_pref] }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>

      </div>
    </div>
    
    
    
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-trash"></i> Delete File</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" id="deletefileForm" method="post" action="{{ action('HostedFileController@deletefile') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="import_file">Select File <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <select class="form-control" style="width: 200px;" name="file">
                        <option></option>
                        @foreach ($files as $file)
                            <option value="{{ $file->id }}">{{ $file->original_file_name }} - {{ $file->getPath() }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" class="btn btn-success" data-toggle="modal" data-target=".deletefile-modal-sm">Delete File</button>
              <div class="modal fade deletefile-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
                          </button>
                          <h4 class="modal-title" id="myModalLabel2">Are you sure?</h4>
                        </div>
                        <div class="modal-body">
                          <h4>Delete File?</h4>
                          <p>This will delete all logs related to this file and cannot be undone!  I recommend you just disable the file instead!</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                          <button type="button" id="deletefile_btn" class="btn btn-danger" style="margin-bottom: 5px;">Delete File</button>
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
    
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Files Found"
      }
    });
    $("#add_file_clear_btn").click(function() {
        $("#name").val('');
        $("#email").val('');
        $("#password").val('');
        $("#phone_number").val('');
    });
    
    $("#deletefile_btn").click(function() {
        $("#deletefileForm").submit();
    });
    
    var url="{{ \Request::root() }}/";
    
    $("#path").keyup(function() {
        var oldVal = $("#path").val();
        oldVal = oldVal.replace(/^\/+/g, '').replace(/[^0-9a-zA-Z_\.%\/]/g, '');
        $("#path").val(oldVal);
        updatePath();
        
        $.post('{{ action('AjaxController@check_route') }}', {'route': $("#path").val()}, function (data) {
            if (data.data == false)
            {
                $("#routeResult").html(" - Taken!")
            }
            else
            {
                $("#routeResult").html("");
            }
        });
        
    });
    
    $("#uid_tracker").keyup(function() {
        var oldVal = $("#uid_tracker").val();
        oldVal = oldVal.replace(/[^0-9a-zA-Z%]/g, '');
        $("#uid_tracker").val(oldVal);
        if (oldVal.length == 0)
        {
          $("#action_on_invalid").slideUp();
        }
        else
        {
          $("#action_on_invalid").slideDown();
        }
        updatePath();
    });
    
    function updatePath()
    {
        var newUrl = url;
        newUrl += $("#path").val();
        if ($("#uid_tracker").val() != "")
        {
            newUrl += "?"+$("#uid_tracker").val()+"=[uid]";
        }
        $("#currentPath").html(newUrl);
    }
    
</script>
@endsection