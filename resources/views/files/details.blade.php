@extends('layouts.app', ['title' => 'Hosted File Summary'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted File Summary</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-file"></i> File Settings</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table class="table table-striped table-bordered" style="width: 100%; margin-left: auto; margin-right: auto;">
            <tbody>
              <tr>
                <td><b>Original File Name</b></td>
                <td>{{ $file->original_file_name }}</td>
              </tr>
              <tr>
                <td><b>File Path</b></td>
                <td>{{ $file->getPathWithVar() }}</td>
              </tr>
              <tr>
                <td><b>Local Path</b></td>
                <td class="tt" title="{{ storage_path($file->local_path) }}">{{ str_limit($file->local_path,30) }}</td>
              </tr>
              <tr>
                <td><b>Mime Type</b></td>
                <td>{{ $file->file_mime }}</td>
              </tr>
              <tr>
                <td><b>Action</b></td>
                <td>{{ $file->getAction() }}</td>
              </tr>
              <tr>
                <td><b>Notifications</b></td>
                <td>
                  @if ($file->notify_access)
                    Enabled
                  @else 
                    Disabled
                  @endif
                </td>
              </tr>
              <tr>
                <td><b>View Count</b></td>
                <td>
                  {{ $file->views()->count() }} / 
                  @if ($file->kill_switch != null)
                    {{ $file->kill_switch }}
                  @else 
                    &infin;
                  @endif
                </td>
              </tr>
              <tr>
                <td><b>Created at</b></td>
                <td>{{ \App\Libraries\DateHelper::readable($file->created_at) }}</td>
              </tr>
            </tbody>
          </table>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel" style="overflow: auto;">
      <div class="x_title">
        <h2><i class="fa fa-eye"></i> List of Views</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table class="table table-striped table-bordered datatable">
              <thead>
                  <tr>
                      <th>IP Address</th>
                      <th>Browser</th>
                      <th>Browser Maker</th>
                      <th>Platform</th>
                      <th>Related User</th>
                      <th>Timestamp</th>
                  </tr>
              </thead>
              <tbody>
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
    var test;
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Files Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@hosted_file_view_table', ['id' => $file->id]) }}",
        type: "POST"
      },
      columns: [
        { data: 'ip', name: 'ip'},
        { data: 'browser', name: 'browser'},
        { data: 'browser_maker', name: 'browser_maker'},
        { data: 'platform', name: 'platform'},
        { data: 'email.targetuser.full_name', name: 'email.targetuser.first_name', render: function(data, type, row) {
            test = row;
            if (data == '')
              return 'N/A';
            return data + " (" + row.email.campaign.name + ")";
          }
        },
        { data: 'created_at', name: 'created_at' },
      ],
      order: [[ 5, 'desc' ]]
    });
    
    CURRENT_URL = "{{ action('HostedFileController@index') }}";
</script>
@endsection