@extends('layouts.app', ['title' => 'Hosted File Summary'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted File Summary - {{ $file->original_file_name }} - 
    @if ($file->action == App\HostedFile::DISABLED)
      <font style="color: #FF0000;"><b>DISABLED</b></font>
    @else 
      <font style="color: #00BB00;">ACTIVE</font>
    @endif
    </h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-4 col-sm-4 col-xs-4">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-info-circle"></i> File Settings</h2>
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
                <td><a href="{{ $file->getFullPath() }}">{{ $file->getPathWithVar() }}</a></td>
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
                @if ($file->action == App\HostedFile::DISABLED)
                  <td><font style="color: #FF0000;">Disabled</font></td>
                @else
                  <td><font style="color: #00BB00;">{{ $file->getAction() }}</font></td>
                @endif
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
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-area-chart"></i> View History</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div id="viewGraph" style="height: 301px;"></div>
      </div>
    </div>
  </div>
  <div class="col-md-2 col-sm-2 col-xs-2">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-tasks"></i> Actions</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table>
            <tbody>
              <tr>
                <td style="padding-bottom:30px;"><a style="width: 200px;" class="btn btn-primary" href="{{ action('HostedFileController@file_details_download', ['id' => $file->id]) }}">Download File</a></td>
              </tr>
              <tr>
                @if ($file->notify_access)
                  <td><a style="width: 200px;" class="btn btn-warning" href="{{ action('HostedFileController@file_details_toggle_notify', ['id' => $file->id]) }}">Turn notifications <b>OFF</b></a></td>
                @else
                  <td><a style="width: 200px;" class="btn btn-warning" href="{{ action('HostedFileController@file_details_toggle_notify', ['id' => $file->id]) }}">Turn notifications <b>ON</b></a></td>
                @endif
              </tr>
              @if ($file->action != App\HostedFile::DISABLED)
                <tr>
                  <td style="padding-top:30px;"><form id="disableForm" action="{{ action('HostedFileController@file_details_disable') }}" method="post">{{ csrf_field() }}<input type="hidden" name="id" value="{{ $file->id }}" /><button style="width: 200px;" class="btn btn-danger" id="disableBtn" type="button">Disable File</button></form></td>
                </tr>
              @endif
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
        { data: 'ip', name: 'ip', render: function(data, type, row) {
          return data + ' (<a target="_blank" href="https://ipinfo.io/'+data+'">whois</a>)';
        }},
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
    
    var dataView = [
      @foreach ($viewGraphData as $view)
        [new Date("{{ $view[0] }}"), {{ $view[1] }}],
      @endforeach
    ];
    
    var dataset = [
      {
        label:"# of Views",
        data: dataView,
        color: "#00FF00",
        points: {show: true},
        lines: {show:true}
      },
    ];
    
    var options = {
    xaxis: {
      label: "Views",
      mode: "time",
      minTickSize: [1, "day"],
      timeformat: "%m/%d/%Y"
    },
    yaxis: {
      minTickSize: 1,
      tickDecimals: 0,
    },
    grid: {
      show: true,
      aboveData: true,
      color: "#3f3f3f",
      labelMargin: 10,
      axisMargin: 0,
      borderWidth: 0,
      borderColor: null,
      minBorderMargin: 5,
      clickable: true,
      hoverable: true,
      autoHighlight: true,
      mouseActiveRadius: 100
    },
    series: {
      lines: {
        show: true,
        fill: true,
        lineWidth: 2,
        steps: false
      },
      points: {
        show: true,
        radius: 4.5,
        symbol: "circle",
        lineWidth: 3.0
      }
    },
  };
    
    var plot = $.plot($("#viewGraph"), dataset, options);
    
    
    
    
    
    $("#disableBtn").click(function() {
        bootbox.confirm("Are you sure you want to disable this file? This cannot be undone and you will have to rehost the file if you want it again!", function(result) {
            if (result)
                $("#disableForm").submit();
        });
    });
    
    
    CURRENT_URL = "{{ action('HostedFileController@index') }}";
</script>
@endsection