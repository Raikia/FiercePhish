@extends('layouts.app', ['title' => 'Files of '.$site->name])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted Files of {{ $site->name }}</h3>
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
                  @if ($site->files()->count() > 0)
                      @foreach ($site->files as $file)
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