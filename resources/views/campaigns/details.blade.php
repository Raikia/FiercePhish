@extends('layouts.app')

@section('content')
<div class="page-title">
    <div class="title_left">
        <h3>Details on &quot;{{ $campaign->name }}&quot; Campaign</h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-5 col-sm-5 col-xs-12">
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
                            <td><b>Email Template</b></td>
                            <td><a href="{{ action('EmailController@template_index', ['id' => $campaign->email_template->id]) }}">{{ $campaign->email_template->name }}</a></td>
                        </tr>
                        <tr>
                            <td><b>Target List</b></td>
                            <td><a href="{{ action('TargetsController@assign_index', ['id' => $campaign->target_list->id]) }}">{{ $campaign->target_list->name }}</a></td>
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
    <div class="col-md-7 col-sm-7 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Email Log</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table id="emailLogTable" class="table table-striped table-bordered datatable">
                    <thead>
                        <tr>
                            <th>Receiver Name</th>
                            <th>Receiver Email</th>
                            <th>UUID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaign->emails as $email)
                            <tr>
                                <td>{{ $email->receiver_name }}</td>
                                <td>{{ $email->receiver_email }}</td>
                                <td>{{ $email->campaign->target_list->users->where('first_name', explode(' ', $email->receiver_name)[0])->where('email', $email->receiver_email)->first()->uuid($campaign) }}
                                <td>{{ $email->getStatus() }}</td>
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
    /* global bootbox */
    $("#cancel_campaign_btn").click(function() {
        bootbox.confirm("Are you sure you want to cancel this campaign?", function(result) {
            $("#cancelForm").submit();
        });
    });
    
    $("#emailLogTable").dataTable();
</script>
@endsection