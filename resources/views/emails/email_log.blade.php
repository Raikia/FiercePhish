@extends('layouts.app', ['title' => 'Email Log'])

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
          <table class="table table-hover table-striped table-bordered datatable pointer" id="mainEmailLogTable">
            <thead>
              <tr>
                <th>Receiver Name</th>
                <th>Receiver Email</th>
                <th>Sender Name</th>
                <th>Sender Email</th>
                <th>Subject</th>
                <th>UUID</th>
                <th>Status</th>
                <th class="no-sort">Campaign</th>
                <th>Planned Send At</th>
                <th>Sent At</th>
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
  $(document).on('click', "#mainEmailLogTable tbody>tr", function(item) {
    window.location="{{ action('EmailController@email_log_details') }}/"+item.currentTarget.id.split('_')[1];
  });
  
  var a = "";
  var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Emails Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@email_log') }}",
        type: "POST"
      },
      columns: [
        { data: 'targetuser.full_name', name: 'targetuser.first_name'},
        { data: 'targetuser.email', name: 'targetuser.email'},
        { data: 'sender_name', name: 'sender_name'},
        { data: 'sender_email', name: 'sender_email'},
        { data: 'subject', name: 'subject'},
        { data: 'uuid', name: 'uuid', render: function ( data, type, row ) {
            if (data)
              return data.substr(0,10)+"...";
            return "";
          }
        },
        { data: 'status', name: 'status'},
        { data: 'campaign.name', name: 'campaign.name', render: function ( data, type, row ) {
            if (data != "None")
              return '<a href="{{ action('CampaignController@campaign_details') }}/'+row.campaign.id+'/">'+data+'</a>';
            return data;
          }
        },
        { data: 'planned_time', name: 'planned_time'},
        { data: 'sent_time', name: 'sent_time'},
      ],
      order: [[ 9, 'desc' ]]
    });
</script>
@endsection