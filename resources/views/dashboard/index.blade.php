@extends('layouts.app')

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
      <div class="count">{{ App\Campaign::where('status', App\Campaign::FINISHED)->count() }}/{{ App\Campaign::all()->count() }}</div>
      <h3>Total Campaigns</h3>
      <p>Number of completed over total campaigns</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-comments-o"></i></div>
      <div class="count">{{ App\Email::where('status', App\Email::SENT)->count() }}/{{ App\Email::all()->count() }}</div>
      <h3>Total Emails</h3>
      <p>Number of sent emails over total emails</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-sort-amount-desc"></i></div>
      <div class="count">{{ App\TargetList::all()->count() }}</div>
      <h3>Total Lists</h3>
      <p>Number of target lists</p>
    </div>
  </div>
  <div class="animated flipInY col-lg-3 col-md-3 col-sm-6 col-xs-12">
    <div class="tile-stats">
      <div class="icon"><i class="fa fa-check-square-o"></i></div>
      <div class="count">{{ App\TargetUser::all()->count() }}</div>
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
        <div class="filter">
          <div id="reportrange" class="pull-right" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
            <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>
            <span>December 30, 2014 - January 28, 2015</span> <b class="caret"></b>
          </div>
        </div>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <div class="col-md-9 col-sm-12 col-xs-12">
          <div class="demo-container" style="height:280px">
            <div id="emailGraph" class="demo-placeholder"></div>
          </div>
          

        </div>

        <div class="col-md-3 col-sm-12 col-xs-12">
          <div>
            <div class="x_title">
              <h2>Recent Emails Sent</h2>
              <div class="clearfix"></div>
            </div>
            <ul class="list-unstyled top_profiles scroll-view">
              @foreach (App\Email::where('status', App\Email::SENT)->orderby('updated_at', 'desc')->limit(5)->get() as $email)
              <li class="media event">
                <div class="media-body" style="padding-left: 10px;">
                  <a class="title" href="{{ action('EmailController@email_log_details', ['id' => $email->id]) }}">{{ $email->receiver_name }}</a>
                  <p>Campaign: {!! ($email->campaign)?'<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>':'None' !!} </p>
                  <p> <small>Sent: {{ $email->updated_at->format('M j, Y @ g:i:s a') }}</small>
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
              <div class="col-md-4">
                <canvas id="canvas1i" height="110" width="110" style="margin: 5px 10px 10px 0"></canvas>
                <h4 style="margin:0">Campaign 1</h4>
              </div>
              <div class="col-md-4">
                <canvas id="canvas1i2" height="110" width="110" style="margin: 5px 10px 10px 0"></canvas>
                <h4 style="margin:0">Campaign 2</h4>
              </div>
              <div class="col-md-4">
                <canvas id="canvas1i3" height="110" width="110" style="margin: 5px 10px 10px 0"></canvas>
                <h4 style="margin:0">Campaign 3</h4>
              </div>
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
/*
// Flot
//define chart clolors ( you maybe add more colors if you want or flot will add it automatic )
        var chartColours = ['#96CA59', '#3F97EB', '#72c380', '#6f7a8a', '#f7cb38', '#5a8022', '#2c7282'];

        //generate random number for charts
        randNum = function() {
          return (Math.floor(Math.random() * (1 + 40 - 20))) + 20;
        };

        var d1 = [];
        //var d2 = [];

        //here we generate data for chart
        for (var i = 0; i < 30; i++) {
          d1.push([new Date(Date.today().add(i).days()).getTime(), randNum() + i + i + 10]);
          //    d2.push([new Date(Date.today().add(i).days()).getTime(), randNum()]);
        }

        var chartMinDate = d1[0][0]; //first day
        var chartMaxDate = d1[20][0]; //last day

        var tickSize = [1, "day"];
        var tformat = "%d/%m/%y";

        //graph options
        var options = {
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
          legend: {
            position: "ne",
            margin: [0, -25],
            noColumns: 0,
            labelBoxBorderColor: null,
            labelFormatter: function(label, series) {
              // just add some space to labes
              return label + '&nbsp;&nbsp;';
            },
            width: 40,
            height: 1
          },
          colors: chartColours,
          shadowSize: 0,
          tooltip: true, //activate tooltip
          tooltipOpts: {
            content: "%s: %y.0",
            xDateFormat: "%d/%m",
            shifts: {
              x: -30,
              y: -50
            },
            defaultTheme: false
          },
          yaxis: {
            min: 0
          },
          xaxis: {
            mode: "time",
            minTickSize: tickSize,
            timeformat: tformat,
            min: chartMinDate,
            max: chartMaxDate
          }
        };
  var plot = $.plot($("#emailGraph"), [{
          label: "Emails Sent",
          data: d1,
          lines: {
            fillColor: "rgba(150, 202, 89, 0.12)"
          }, //#96CA59 rgba(150, 202, 89, 0.42)
          points: {
            fillColor: "#fff"
          }
        }], options);





 // Doughnut Chart
  var canvasDoughnut,
            options = {
              legend: false,
              responsive: false
            };

        new Chart(document.getElementById("canvas1i"), {
          type: 'doughnut',
          tooltipFillColor: "rgba(51, 51, 51, 0.55)",
          data: {
            labels: [
              "Symbian",
              "Blackberry",
              "Other",
              "Android",
              "IOS"
            ],
            datasets: [{
              data: [15, 20, 30, 10, 30],
              backgroundColor: [
                "#BDC3C7",
                "#9B59B6",
                "#E74C3C",
                "#26B99A",
                "#3498DB"
              ],
              hoverBackgroundColor: [
                "#CFD4D8",
                "#B370CF",
                "#E95E4F",
                "#36CAAB",
                "#49A9EA"
              ]

            }]
          },
          options: options
        });

*/


  var latestActivityLog = {{ ($activitylog->count() > 0)?$activitylog[0]->id:-1 }};
  $(document).ready(function() {
    window.setInterval(grabActivityLog, 2000);
  });



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