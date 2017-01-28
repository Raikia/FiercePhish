@extends('layouts.app', ['title' => 'Dashboard'])

@section('content')
<!--<div class="container">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                    You are logged in!
                </div>
            </div>
        </div>
    </div>
</div>-->
<div class="page-title">
  <div class="title_left">
    <h3>Dashboard</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row top_tiles">
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-caret-square-o-right"></i></div>
      <div class="count">{{ number_format(App\Campaign::where('status', App\Campaign::FINISHED)->count()) }}/{{ number_format(App\Campaign::count()) }}</div>
      <h3>Total Campaigns</h3>
      <p>Number of completed over total campaigns</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-envelope-o"></i></div>
      <div class="count">{{ number_format(App\Email::where('status', App\Email::SENT)->count()) }}/{{ number_format(App\Email::count()) }}</div>
      <h3>Total Emails</h3>
      <p>Number of sent emails over total emails</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-list"></i></div>
      <div class="count">{{ number_format(App\TargetList::count()) }}</div>
      <h3>Total Lists</h3>
      <p>Number of target lists</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-user-o"></i></div>
      <div class="count">{{ number_format(App\TargetUser::where('hidden', false)->count()) }}</div>
      <h3>Total Users</h3>
      <p>Number of target users</p>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Email Sending Summary <small>Daily progress</small></h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="col-md-9 col-sm-12 col-xs-12">
          <div class="demo-container" style="height:280px">
            <div id="emailGraph" style="height: 280px;"></div>
            <div class="tiles">
                        <div class="col-md-4 tile">
                          <span>Emails Sent</span>
                          <h2>{{ number_format($emailStats['numSent']) }}</h2>
                        </div>
                        <div class="col-md-4 tile">
                          <span>Emails Cancelled</span>
                          <h2>{{ number_format($emailStats['numCancelled']) }}</h2>
                        </div>
                        <div class="col-md-4 tile">
                          <span>Emails Awaiting Sending</span>
                          <h2>{{ number_format($emailStats['numPending']) }}</h2>
                        </div>
                      </div>
          </div>
          

        </div>

        <div class="col-md-3 col-sm-12 col-xs-12">
          <div>
            <div class="x_title">
              <h2>Recently Sent Emails</h2>
              <div class="clearfix"></div>
            </div>
            <ul class="list-unstyled top_profiles scroll-view">
              @foreach (App\Email::with('targetuser')->where('status', App\Email::SENT)->orderby('updated_at', 'desc')->limit(5)->get() as $email)
              <li class="media event">
                <div class="media-body" style="padding-left: 10px;">
                  <a class="title" href="{{ action('EmailController@email_log_details', ['id' => $email->id]) }}">{{ $email->targetuser->full_name() }}</a>
                  <p>Campaign: {!! ($email->campaign)?'<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>':'None' !!} </p>
                  <p> <small>Sent: {{ \App\Libraries\DateHelper::readable($email->sent_time) }} ({{ \App\Libraries\DateHelper::relative($email->sent_time) }})</small>
                  </p>
                </div>
              </li>
              @endforeach
            </ul>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Active Campaigns</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="row" style="border-bottom: 1px solid #E0E0E0; padding-bottom: 5px; margin-bottom: 5px;">
          <div class="col-md-12">
            <div class="row" style="text-align: center;">
            @foreach ($allActiveCampaigns as $campaign)
              <div class="col-md-4">
                <div id="campaign{{ $campaign->id}}pie" style="height: 110px; width: 110px; margin-left: auto; margin-right: auto; margin-bottom: 10px;"></div>
                <h4 style="margin:0; margin-bottom: 20px;">{{ $campaign->name }}</h4>
              </div>
            @endforeach
            @if (count($allActiveCampaigns) == 0)
              <div class="col-md-12">
                <h2>No Active Campaigns</h2>
              </div>
            @else
            </div>
            <div class="row" style="text-align: center;">
              <div class="col-md-12">
                <table style="margin-left: auto; margin-right: auto; border: 1px solid #bfbfbf; text-align: left;">
                  <tr>
                  <td style="padding: 10px 10px 10px 10px; padding-right: 20px; vertical-align: top;">Legend:</td>
                  <td style="padding: 10px 10px 10px 10px;"><div style="width: 10px; height: 10px; float: left; margin-top: 4px; margin-right: 7px; background-color: #00FF00; border: 1px solid #bfbfbf;"></div>Emails Sent<br />
                  <div style="width: 10px; height: 10px; float: left; margin-top: 4px; margin-right: 7px; background-color: #FF0000; border: 1px solid #bfbfbf;"></div>Emails Cancelled<br />
                  <div style="width: 10px; height: 10px; float: left; margin-top: 4px; margin-right: 7px; background-color: #0000FF; border: 1px solid #bfbfbf;"></div>Emails Pending</td>
                  </tr>
                </table>
            @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Activity Log</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
<pre style="height: 300px; overflow: auto;" id="activityLog">
@foreach ($activitylog as $act)
{{ $act->read() }}
@endforeach
</pre>
      </div>
    </div>
  </div>
</div>
@endsection



@section('footer')
<script type="text/javascript">


$(document).ready(function() {

/* Plot the graph for sent emails */
  var dataSent = [
  @foreach ($sendEmailData as $email)
    [new Date("{{ $email->date }}"), {{ $email->numEmails }}],
  @endforeach
  ];

  var dataError = [
  @foreach ($errorEmailData as $email)
    [new Date("{{ $email->date }}"), {{ $email->numEmails }}],
  @endforeach
  ];

  var dataset = [
    {
      label: "Emails Cancelled",
      data: dataError,
      color: "#FF0000",
      points: {show: true},
      lines: {show: false, fill: false}
    },
    {
      label: "Emails Sent",
      data: dataSent,
      color: "#00FF00",
      points: {show: true},
      lines: {show: true}
    },
  ];
  var options = {
    xaxis: {
      mode: "time",
      minTickSize: [1, "day"],
      timeformat: "%m/%e/%Y"
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
  var plot = $.plot($("#emailGraph"), dataset, options);

/* Plot the campaign pie charts */
@foreach ($allActiveCampaigns as $campaign)
  var camp{{ $campaign->id}}dataset = [
    {
      label: "Emails Cancelled",
      data: {{ $campaign->emails()->where('status', \App\Email::CANCELLED)->orWhere('status', \App\Email::FAILED)->count() }},
      color: "#FF0000"
    },
    {
      label: "Emails Pending",
      data: {{ $campaign->emails()->where('status', \App\Email::NOT_SENT)->orWhere('status', \App\Email::SENDING)->orWhere('status', \App\Email::PENDING_RESEND)->count() }},
      color: "#0000FF"
    },
    {
      label: "Emails Sent",
      data: {{ $campaign->emails()->where('status', \App\Email::SENT)->count() }},
      color: "#00FF00"
    }
  ];

  var camp{{ $campaign->id }} = $.plot($("#campaign{{ $campaign->id}}pie"), camp{{ $campaign->id }}dataset, {
    series: {
      pie: {
        innerRadius: 0.5,
        show: true,
        label: {
          show: false
        }
      }
    },
    legend: {
      show: false
    }
  });
@endforeach


  window.setInterval(grabActivityLog, 2000);
});

var latestActivityLog = {{ ($activitylog->count() > 0)?$activitylog[0]->id:-1 }};

function grabActivityLog()
{
  $.get("{{ action('AjaxController@get_activitylog') }}/"+latestActivityLog, function(results) {
    for (var x = 0; x < results.data.length; ++x)
    {
      $("#activityLog").prepend(results.data[x] + "<br />");
    }
    latestActivityLog = results.latest_id;
  });
}
</script>
@endsection