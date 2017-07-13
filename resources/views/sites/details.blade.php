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

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-key"></i> Harvested Credentials</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Username</th>
              <th>Password</th>
              <th>Related User</th>
              <th>Date Received</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($file->credentials as $cred)
              <tr>
                <td>{{ $cred->username }}</td>
                <td>{{ $cred->password }}</td>
                @if ($cred->view->email !== null && $cred->view->email->targetuser !== null)
                  <td>{{ $cred->view->email->targetuser }}</td>
                @else 
                  <td>N/A</td>
                @endif
                <td>{{ App\Libraries\DateHelper::readable($cred->created_at) }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="text-align: center;">No credentials yet</td>
              </tr>
            @endforelse
          </tbody>
        </table>

      </div>
    </div>
  </div>
</div>

<div class="modal fade geolocate-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">Geolocation of <span id="geolocate_modal_ip"></span></h4>
      </div>
      <div class="modal-body">
        <div style="width: 500px; margin-left: auto; margin-right: auto; overflow: hidden; height: 320px; margin-bottom:30px; border: 1px solid black;" id="geolocate_gmaps_container">
        <iframe
          width="500"
          height="620"
          id="geolocate_gmaps"
          frameborder="0" style="border:0px;margin-top: -150px;"
          src="https://www.google.com/maps/embed/v1/place?key=AIzaSyCKsOvH7ZnhQaiEqDOEV3RZBws2Jy_Qfaw&zoom=9&q=0.0,0.0">
        </iframe>
        </div>
        <table class="table table-bordered" style="width: 500px; margin-left: auto; margin-right: auto;">
          <tbody>
            <tr>
              <td style="width: 120px;"><b>IP</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_ip"></span></td>
            </tr>
            <tr>
              <td><b>Country Name</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_country_name"></span></td>
            </tr>
            <tr>
              <td><b>Region Name</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_region_name"></span></td>
            </tr>
            <tr>
              <td><b>City</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_city"></span></td>
            </tr>
            <tr>
              <td><b>Zip Code</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_zip_code"></span></td>
            </tr>
            <tr>
              <td><b>Latitude</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_latitude"></span></td>
            </tr>
            <tr>
              <td><b>Longitude</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_longitude"></span></td>
            </tr>
            <tr>
              <td><b>Timezone</b></td>
              <td><span style="line-height: normal;" id="geolocate_modal_table_time_zone"></span></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Done</button>
      </div>

    </div>
  </div>
</div>



@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    var test = [];
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
          var geolocate = '  (<a href="http://ipinfo.io/'+data+'" target="_blank">geolocate</a>)';
          if (row.geolocate != "" && row.geolocate != null)
          {
            geolocate = '  (<a href="javascript:void()" class="geolocatepopup" id="'+row.id+'_'+row.geolocate.ip+'">geolocate</a>)';
          }
          return data + ' (<a target="_blank" href="https://ipinfo.io/'+data+'">whois</a>)'+geolocate;
        }},
        { data: 'browser', name: 'browser'},
        { data: 'browser_maker', name: 'browser_maker'},
        { data: 'platform', name: 'platform'},
        { data: 'email.targetuser.full_name', name: 'email.targetuser.first_name', render: function(data, type, row) {
            var creds = "";
            if (row.credentials != null)
              creds = '&nbsp;&nbsp;&nbsp;<i class="fa fa-key" title="' + row.credentials.username + ' : ' + row.credentials.password + '"></i>'
            if (data == '')
              return 'N/A' + creds;
            return data + " (" + row.email.campaign.name + ")" + creds;
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
    
    
    
    
  
    
    var geolocate_data = {!! json_encode($geoData) !!};
    
    $(document).on('click', '.geolocatepopup', function(e) {
      var id = $(e.currentTarget).attr('id');
      var ip = id.split("_")[1];
      
      if (geolocate_data[ip] === undefined)
      {
        bootbox.alert("The geolocation information for that IP address was not found.  Please refresh the page and try again");
      }
      else
      {
        var displayData = ['ip', 'country_name', 'region_name', 'city', 'zip_code', 'latitude', 'longitude', 'time_zone'];
        $("#geolocate_modal_ip").html(ip);
        if (geolocate_data[ip]["latitude"] === 0 || geolocate_data[ip]["longitude"] === 0)
        {
          $("#geolocate_gmaps_container").hide();
        }
        else
        {
          $("#geolocate_gmaps").attr('src', 'https://www.google.com/maps/embed/v1/place?key=AIzaSyCKsOvH7ZnhQaiEqDOEV3RZBws2Jy_Qfaw&zoom=9&q='+geolocate_data[ip]["latitude"]+','+geolocate_data[ip]["longitude"]);
          $("#geolocate_gmaps_container").show();
        }
        for (var x=0; x<displayData.length; ++x)
        {
          var dataToWrite = geolocate_data[ip][displayData[x]];
          if (dataToWrite == "" || dataToWrite == "0")
          {
            dataToWrite = "Unknown";
          }
          if (displayData[x] == "ip")
          {
            dataToWrite +=' (<a target="_blank" href="https://ipinfo.io/'+ip+'">whois</a>)';
          }
          $("#geolocate_modal_table_"+displayData[x]).html(dataToWrite);
        }
        $(".geolocate-modal").modal('show');
      }
      return false;
    });
    
    CURRENT_URL = "{{ action('HostedSiteController@index') }}";
</script>
@endsection