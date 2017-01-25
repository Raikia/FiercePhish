@extends('layouts.app', ['title' => 'Create Campaign'])

@section('content')
<div class="page-title">
  <div class="title_left">
    <h3>Create New Phishing Campaign</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <form class="form-horizontal form-label-left input_mask">

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Campaign Name&nbsp;<span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="campaign_name_raw" placeholder="">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Campaign Description</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <textarea rows="5" class="form-control" style="width: 100%" id="campaign_description_raw"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Email Template <span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    @if (count($templates) > 0)
                      <select class="form-control select2_single" id="email_template_raw">
                        <option></option>
                      @foreach ($templates as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                      @endforeach
                    </select>
                    @else
                      <p style="color: #ff0000; padding-top: 7px;">No templates found</p>
                    @endif
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Target List <span class="required">*</span>
                </label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  @if (count($lists) > 0)
                    <select class="form-control select2_single" id="target_list_raw">
                      <option></option>
                      @foreach ($lists as $item)
                        <option value="{{ $item->id }}">{{ $item->name }} - {{ number_format($item->users()->count()) }} users</option>
                      @endforeach
                    </select>
                  @else
                    <p style="color: #ff0000; padding-top: 7px;">No target list found</p>
                  @endif
                </div>
              </div>
            </form>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <form class="form-horizontal form-label-left input_mask">
              <div class="form-group date">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sender Name</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="sender_name_raw" placeholder="Bill Smith" />
                </div>
              </div>
              <div class="form-group date">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sender Email</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="email" class="form-control" id="sender_email_raw" placeholder="bsmith@malicious.com" />
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sending Schedule</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="radio">
                        <label>
                            <input type="radio" id="sending_schedule_raw" value="all" name="scheduleSelect" onclick="$('#send_num_raw').val('');$('#send_every_x_minutes_raw').val('');" checked /> All at once
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <div class="form-group">
                                <input type="radio" style="margin-top: 20px;" id="sending_schedule_custom" name="scheduleSelect" />
                            </div>
                            <div class="col-md-5 col-sm-5 col-xs-10 form-group">
                                <input type="number" min="1" id="send_num_raw" class="form-control" onfocus="$('#sending_schedule_custom').prop('checked','checked');" placeholder="Send # emails" /> 
                            </div>
                            <div class="col-md-5 col-sm-5 col-xs-10 form-group">
                                <input type="number" min="1" id="send_every_x_minutes_raw" class="form-control" onfocus="$('#sending_schedule_custom').prop('checked','checked');" placeholder="Every # minutes" />
                            </div>
                        </label>
                    </div>
                </div>
              </div>
              <div class="form-group date">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Starting date</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="starting_date_raw" placeholder="Today" value="{{ \App\Libraries\DateHelper::now()->format('m/d/Y') }}" />
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Starting time</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="starting_time_raw" placeholder="Now" value="{{ \App\Libraries\DateHelper::now()->format('g:ia') }}" />
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">&nbsp;</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <p>
                  Current server date and time is {{ \App\Libraries\DateHelper::now()->format('m/d/Y - g:ia') }}
                  </p>
                </div>
              </div>
            </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_content" style="text-align: center;">
        <form action="{{ action('CampaignController@create_post') }}" method="post" id="campaign_form">
          <button type="submit" class="btn btn-success" style="margin-right: 20px;">Launch Campaign!</button>
          <a class="btn btn-primary" style="margin-left: 20px;" href="{{ action('DashboardController@index') }}">Cancel!</a>
          <input type="hidden" name="campaign_name" />
          <input type="hidden" name="campaign_description" />
          <input type="hidden" name="email_template" />
          <input type="hidden" name="target_list" />
          <input type="hidden" name="sender_name" />
          <input type="hidden" name="sender_email" />
          <input type="hidden" name="sending_schedule" />
          <input type="hidden" name="send_num" />
          <input type="hidden" name="send_every_x_minutes" />
          <input type="hidden" name="starting_date" />
          <input type="hidden" name="starting_time" />
          {{ csrf_field() }}
        </form>
      </div>
    </div>
  </div>
</div>
@endsection



@section('footer')
<script type="text/javascript">
/* global $ */
/* global bootbox */
  $("#email_template_raw").select2({
    placeholder: "Select an Email Template",
    allowClear: true
  });
  $("#target_list_raw").select2({
    placeholder: "Select an Target List",
    allowClear: true
  });
  
  $("#starting_date_raw").datepicker({
    startDate: "{{ \App\Libraries\DateHelper::now()->format('m/d/Y') }}",
    todayHighlight: true,
    autoclose: true,
    todayBtn: "linked",
  });
  
  $("#starting_time_raw").timepicker({
    scrollDefault: "now",
    step: 5,
  });
  
  
  $("#campaign_form").submit(function() {
    var fields = ['campaign_name', 'campaign_description', 'email_template', 'target_list', 'sender_name', 'sender_email', 'send_num', 'send_every_x_minutes', 'starting_date', 'starting_time'];
    for (var x=0; x < fields.length; ++x)
    {
      $("input[name="+fields[x]+"]").val($("#"+fields[x]+"_raw").val());
    }
    
    if ($("#sending_schedule").prop('checked'))
      $("input[name=sending_schedule]").val('all');
    
    var required_fields = { 'campaign_name': 'Campaign Name', 'campaign_description': 'Campaign Description', 'email_template': 'Email Template', 'target_list': 'Target List', 'sender_name': 'Sender Name', 'sender_email': 'Sender Email' };
    var keys_fields = Object.keys(required_fields);
    for (var x=0; x < keys_fields.length; ++x)
    {
      if ($("input[name="+keys_fields[x]+"]").val() == "") {
        bootbox.alert("Error: " + required_fields[keys_fields[x]] + " is required");
        return false;
      }
    }
    if ($("#sending_schedule_custom").prop('checked'))
    {
      if ($("input[name=send_num]").val() == "" || $("input[name=send_every_x_minutes]").val() == "")
      {
        bootbox.alert("Error: For a custom sending schedule, make sure you input the frequency you want the emails to be sent");
        return false;
      }
    }
    return true;
  });
</script>
@endsection