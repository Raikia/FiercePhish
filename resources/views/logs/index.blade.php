@extends('layouts.app', ['title' => 'Log Viewer'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Raw System Logs</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-7 col-sm-7 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
            <h2>Log Information</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
            To view the logs, you must set the proper permissions on the log files. "{{ exec('whoami') }}" must have "read" access to the log files.<br />
            To set permissions, run: "chown {{ exec('whoami') }}:{{ exec('whoami') }} &lt;log_file&gt;"<br /><br />
            Logs this will show:
            <br />
            <ul>
                @foreach ($logs as $name => $log)
                <li>
                    {{ $log }}
                    @if (!is_readable($log))
                        &nbsp;&nbsp;(<span style="color: #FF0000;">Invalid file permissions or file does not exist!</span>)
                    @endif
                </li>
                @endforeach
                <li>FiercePhish Activity Log</li>
            </ul>
      </div>
    </div>
  </div>
  <div class="col-md-5 col-sm-5 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Download Logs</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table class="table table-striped table-bordered">
              <tbody>
             @foreach ($logs as $name => $loc)
                @if (is_readable($loc))
                <tr>
                    <td style="vertical-align:middle;"><b>"{{ $loc }}"</b></td>
                    <td style="vertical-align:middle;"><a href="{{ action('LogController@download', ['type' => $name]) }}" class="btn btn-primary">Download log</a></td>
                </tr>
                @endif
            @endforeach
                <tr>
                    <td style="vertical-align:middle;"><b>Activity Log</b></td>
                    <td style="vertical-align:middle;"><a href="{{ action('LogController@download', ['type' => 'activitylog']) }}" class="btn btn-primary">Download log</a></td>
                </tr>
                <tr>
                    <td colspan="2" style="vertical-align: middle; text-align: center;"><a href="{{ action('LogController@download', ['type' => 'all']) }}" class="btn btn-primary">Download All Logs</a></td>
                </tr>
            </tbody>
          </table>
      </div>
    </div>
  </div>
</div>

@foreach ($logs as $name => $loc)
@if (is_readable($loc))
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Last 200 lines of {{ $loc }}</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          @php
              $retArr = [];
          @endphp
          @if (exec('tail -n 200 '.$loc, $retArr))
            <pre style="max-height: 400px;">{{ implode("\n", $retArr) }}</pre>
          @else
            <pre style="max-height: 400px;">Empty log file</pre>
          @endif
      </div>
    </div>
  </div>
</div>
@endif
@endforeach
<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Last 200 lines of Activity Log</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <pre style="max-height: 400px;">{{ $activitylog }}</pre>
      </div>
    </div>
  </div>
</div>

@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    
    
</script>
@endsection