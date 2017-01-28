@extends('layouts.app', ['title' => 'Campaign Details'])

@section('content')
<div class="page-title">
    <div class="title_left">
        <h3>Details on &quot;{{ $campaign->name }}&quot; Campaign</h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-4 col-sm-4 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Campaign Overview</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-striped table-bordered" style="width: 100%; margin-left: auto; margin-right: auto;">
                    <tbody>
                        <tr>
                            <td style="width: 27%;"><b>Campaign Name</b></td>
                            <td>{{ $campaign->name }}</td>
                        </tr>
                        <tr>
                            <td><b>Campaign Description</b></td>
                            <td>{{ $campaign->description }}</td>
                        </tr>
                        <tr>
                            <td><b>Status</b></td>
                            <td>{{ $campaign->getStatus() }}</td>
                        </tr>
                        <tr>
                            <td><b>Emails Sent</b></td>
                            <td>{{ number_format($campaign->emails()->where('status', \App\Email::SENT)->count()) }} / {{ number_format($campaign->emails()->count()) }} sent</td>
                        </tr>
                        <tr>
                            <td><b>Email Template</b></td>
                            <td><a href="{{ action('EmailController@template_index', ['id' => $campaign->email_template->id]) }}">{{ $campaign->email_template->name }}</a></td>
                        </tr>
                        <tr>
                            <td><b>Target List</b></td>
                            <td><a href="{{ action('TargetsController@targetlists_details', ['id' => $campaign->target_list->id]) }}">{{ $campaign->target_list->name }}</a></td>
                        </tr>
                        <tr>
                            <td><b>Sender Name</b></td>
                            <td>{{ $campaign->from_name }}</td>
                        </tr>
                        <tr>
                            <td><b>Sender Email</b></td>
                            <td>{{ $campaign->from_email }}</td>
                        </tr>
                    </tbody>
                </table>
                @if ($campaign->status != \App\Campaign::FINISHED && $campaign->status != \App\Campaign::CANCELLED)
                    <div style="text-align: center;">
                        <button class="btn btn-danger" id="cancel_campaign_btn" name="cancel_campaign">Cancel Campaign</button>
                    </div>
                    <form action="{{ action('CampaignController@campaign_cancel', ['id' => $campaign->id ]) }}" id="cancelForm" method="post">
                        {{ csrf_field() }}
                        <input type="hidden" name="action" value="cancel">
                    </form>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8 col-sm-8 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Email Log</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table id="emailLogTable" class="table table-striped table-bordered datatable pointer">
                    <thead>
                        <tr>
                            <th>Receiver Name</th>
                            <th>Receiver Email</th>
                            <th>UUID</th>
                            <th>Status</th>
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
    /* global bootbox */
    $("#cancel_campaign_btn").click(function() {
        bootbox.confirm("Are you sure you want to cancel this campaign?", function(result) {
            if (result)
                $("#cancelForm").submit();
        });
    });
    
    $(document).ready(function() {
        $("#emailLogTable tbody>tr").css('cursor', 'pointer');
        $(document).on('click',"#emailLogTable tbody>tr", function(item) {
            window.location="{{ action('EmailController@email_log_details') }}/"+item.currentTarget.id.split('_')[1];
        }); 
    });

    
    
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Campaign Emails Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@campaign_emails_get', ['id' => $campaign->id]) }}",
        type: "POST"
      },
      columns: [
        { data: 'targetuser.full_name', name: 'targetuser.first_name'},
        { data: 'targetuser.email', name: 'targetuser.email'},
        { data: 'uuid', name: 'uuid', render: function ( data, type, row ) {
            if (data)
              return data.substr(0,10)+"...";
            return "";
          }},
        { data: 'status', name: 'status'},
        { data: 'planned_time', name: 'planned_time'},
        { data: 'sent_time', name: 'sent_time'},
      ]
    });
    
    @if ($campaign->status != \App\Campaign::WAITING && $campaign->status != \App\Campaign::SENDING)
        CURRENT_URL = "{{ action('CampaignController@index') }}";
    @endif
</script>
@endsection