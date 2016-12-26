@extends('layouts.app')

@section('content')
<div class="page-title">
  <div class="title_left">
    <h3>Email Log</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>List of All Emails</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table class="table table-hover table-striped table-bordered datatable" id="mainEmailLogTable">
            <thead>
              <tr>
                <th>Receiver Name</th>
                <th>Receiver Email</th>
                <th>Sender Name</th>
                <th>Sender Email</th>
                <th>Subject</th>
                <th>Status</th>
                <th>Campaign</th>
                <th>Created At</th>
                <th>Updated At</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($all_emails as $email)
                <tr id="{{ $email->id }}">
                  <td>{{ $email->receiver_name }}</td>
                  <td>{{ $email->receiver_email }}</td>
                  <td>{{ $email->sender_name }}</td>
                  <td>{{ $email->sender_email }}</td>
                  <td>{{ $email->subject }}</td>
                  <td>{{ $email->getStatus() }}</td>
                  <td>{!! ($email->campaign)?'<a href="'.action('CampaignController@campaign_details', ['id' => $email->campaign->id]).'">'.e($email->campaign->name).'</a>':'None' !!}</td>
                  <td>{{ $email->created_at->format('M j, Y @ g:i:s a') }}</td>
                  <td>{{ $email->updated_at->format('M j, Y @ g:i:s a') }}</td>
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
  $("#mainEmailLogTable tbody>tr").css('cursor', 'pointer');
  $("#mainEmailLogTable tbody>tr").click(function(item) {
    window.location="{{ action('EmailController@email_log_details') }}/"+item.currentTarget.id;
  });
  $("#mainEmailLogTable").dataTable();
</script>
@endsection