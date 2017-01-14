@extends('layouts.app', ['title' => 'Email Details'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Email Details</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-download"></i> Email Preview</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <div class="outlook_window">
            <div class="outlook_header">
              <img style="height: 72px; width: 72px; float: left; display: block;" src="{{ asset('images/outlook_person.png') }}" />
              <div class="outlook_metadata" style="margin-left: 100px;">
                <div class="outlook_date">{{ $email->updated_at->format('D m/d/Y h:i a') }}</div>
                <div class="outlook_from">{{ $email->sender_name }} &lt;{{ $email->sender_email }}&gt;</div>
                <div class="outlook_subject" id="outlook_subject">{{ $email->subject }}</div>
              </div>
              <div class="outlook_to"><span style="margin-right: 10px;">To:</span>{{ $email->receiver_name }} &lt;{{ $email->receiver_email }}&gt;</div>
              <div class="outlook_message">
                <span id="outlook_content">
                <!-- ummm persistent xss vulnerability lol, do we care though? I don't think so -->
                  {!! $email->message !!}
                  @if ($email->has_attachment)
                    <br />---<br /><br />Attached: <a href="">{{ $email->attachment_name }}</a> ({{ $email->attachment_mime }})
                  @endif
                </span>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Email Source</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <pre>{{ $email->message }}</pre>
      </div>
    </div>
    
    <div class="x_panel">
      <div class="x_title">
        <h2>Logs around Email Timestamp</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        @if (count($logs) != 0)
        <pre>
          @foreach ($logs as $log)
[{{ $log->log_type }}] {{ $log->log_time}}    {{ $log->data }}
@endforeach
</pre>
        @else 
          <pre>No logs yet</pre>
        @endif
      </div>
    </div>
  </div>
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>Email Metadata</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table class="table table-striped table-bordered" style="width: 100%; margin-left: auto; margin-right: auto;">
          <tbody>
            <tr>
              <td>Sender Name</td>
              <td>{{ $email->sender_name }}</td>
            </tr>
            <tr>
              <td>Sender Email</td>
              <td>{{ $email->sender_email }}</td>
            </tr>
            <tr>
              <td>Receiver Name</td>
              <td>{{ $email->receiver_name }}</td>
            </tr>
            <tr>
              <td>Receiver Email</td>
              <td>{{ $email->receiver_email }}</td>
            </tr>
            @if ($email->has_attachment)
              <tr>
                <td>Attachment Name</td>
                <td>{{ $email->attachment_name }}</td>
              </tr>
              <tr>
                <td>Attachment Mime Type</td>
                <td>{{ $email->attachment_mime }}</td>
              </tr>
            @endif
            <tr>
              <td>UUID</td>
              <td>{{ $email->uuid }}</td>
            </tr>
            <tr>
              <td>Status</td>
              <td>{{ $email->getStatus() }}</td>
            </tr>
            <tr>
              <td>Associated Campaign</td>
              <td>{!! ($email->campaign)?'<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>':'None' !!}</td>
            </tr>
            <tr>
              <td>Created At</td>
              <td>{{ $email->created_at->format('M j, Y @ g:i:s a') }}</td>
            </tr>
            <tr>
              <td>Updated At</td>
              <td>{{ $email->updated_at->format('M j, Y @ g:i:s a') }}</td>
            </tr>
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
    
    
</script>
@endsection