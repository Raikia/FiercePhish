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
                <div class="outlook_date">{{ \App\Libraries\DateHelper::format($email->sent_time, 'D m/d/Y h:i a') }}</div>
                <div class="outlook_from">{{ $email->sender_name }} &lt;{{ $email->sender_email }}&gt;</div>
                <div class="outlook_subject" id="outlook_subject">{{ $email->subject }}</div>
              </div>
              <div class="outlook_to"><span style="margin-right: 10px;">To:</span>{{ $email->targetuser->full_name() }} &lt;{{ $email->targetuser->email }}&gt;</div>
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
        <h2>Related SMTP Logs</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        @if ($email->related_logs !== null)
          <pre>{{ $email->related_logs }}</pre>
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
              <td><b>Sender Name</b></td>
              <td>{{ $email->sender_name }}</td>
            </tr>
            <tr>
              <td><b>Sender Email</b></td>
              <td>{{ $email->sender_email }}</td>
            </tr>
            <tr>
              <td><b>Receiver Name</b></td>
              <td>{{ $email->targetuser->full_name() }}</td>
            </tr>
            <tr>
              <td><b>Receiver Email</b></td>
              <td>{{ $email->targetuser->email }}</td>
            </tr>
            @if ($email->has_attachment)
              <tr>
                <td><b>Attachment Name</b></td>
                <td>{{ $email->attachment_name }}</td>
              </tr>
              <tr>
                <td><b>Attachment Mime Type</b></td>
                <td>{{ $email->attachment_mime }}</td>
              </tr>
            @endif
            <tr>
              <td><b>UUID</b></td>
              <td>{{ $email->uuid }}</td>
            </tr>
            <tr>
              <td><b>Status</b></td>
              <td>{{ $email->getStatus() }}</td>
            </tr>
            <tr>
              <td><b>Associated Campaign</b></td>
              <td>{!! ($email->campaign)?'<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>':'None' !!}</td>
            </tr>
            <tr>
              <td><b>Planned Send At</b></td>
              <td>{{ \App\Libraries\DateHelper::readable($email->planned_time) }}</td>
            </tr>
            <tr>
              <td><b>Sent At</b></td>
              <td>{{ \App\Libraries\DateHelper::readable($email->sent_time) }}</td>
            </tr>
            <tr>
              <td><b>Created At</b></td>
              <td>{{ \App\Libraries\DateHelper::readable($email->created_at) }}</td>
            </tr>
            <tr>
              <td><b>Updated At</b></td>
              <td>{{ \App\Libraries\DateHelper::readable($email->updated_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      <div style="text-align: center; margin-bottom: 20px;">
        @if ($email->status == App\Email::SENT || $email->status == App\Email::CANCELLED || $email->status == App\Email::FAILED)
          @if ($email->campaign == null || $email->campaign->status != App\Campaign::CANCELLED)
            <form action="{{ action('EmailController@email_resend', ['id' => $email->id]) }}" method="post">
              {{ csrf_field() }}
              <input class="btn btn-primary" type="submit" value="Resend Email Immediately" />
            </form>
          @endif
        @elseif ($email->status == App\Email::NOT_SENT || $email->status == App\Email::PENDING_RESEND)
        <form action="{{ action('EmailController@email_cancel', ['id' => $email->id]) }}" method="post">
          {{ csrf_field() }}
          <input class="btn btn-danger" type="submit" value="Cancel Email" />
        </form>
        @endif
      </div>
    </div>
  </div>
</div>


@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    
    CURRENT_URL = "{{ action('EmailController@email_log') }}";
</script>
@endsection