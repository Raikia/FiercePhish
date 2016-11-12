@extends('layouts.app')

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Check Email Settings</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <p>This test will check to see if the DNS records are properly configured to successfully bypass spam filters. Enter a domain below to see if you can spoof an email from it.</p>
          <p>For example, if you plan to send emails from "john.doe@google.com", enter "google.com"</p>
          <form class="form-horizontal form-label-left" method="post" action="" id="check_domain_form">
          
              <div class="form-group" style="margin-top: 38px;">
                <label for="domain" class="control-label col-md-3 col-sm-3 col-xs-12">Domain <span class="required">*</span></label>
                <div class="col-md-6 col-sm-6 col-xs-12">
                  <input id="domain" class="form-control col-md-7 col-xs-12"  placeholder="google.com" required="required" type="text" name="domain">
                </div>
              </div>
              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                  <button type="button" id="check_domain" class="btn btn-success">Check Domain</button>
                  <span style="#FF0000" id="error"></span>
                </div>
              </div>
          
            </form>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel" style="overflow: auto;">
      <div class="x_content">
          <table id="settingsCheckTable" class="table">
              <thead>
                  <tr>
                      <th>Configuration</th>
                      <th>Status</th>
                  </tr>
              </thead>
              <tbody>
                  @foreach ($settingsCheck as $function => $check)
                      <tr>
                          <td>{{ $check }}</td>
                          <td style="font-size: 16pt;" id="{{ $function }}_result" class="ajax_results"></td>
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
    
    function checkDomain() {
        $(".ajax_results").html('<i class="fa fa-refresh fa-spin"></i>');
        var listOfFunctions = [];
        var tableRows = $("#settingsCheckTable tr td:nth-child(2)");
        for (var x=0; x < tableRows.length; ++x)
        {
            listOfFunctions.push(tableRows[x].id.replace("_result",""));
        }
        console.log(listOfFunctions);
        startProcess(listOfFunctions);
    }
    var errors = [];
    function startProcess(listOfFunctions)
    {
        if (listOfFunctions.length == 0)
            return;
        if ($("#domain").val() == "")
        {
            $("#error").html("You must type in a domain!");
            return;
        }
        $("#error").html("");
        var first_process = listOfFunctions.shift();
        
        $.get("{{ action('AjaxController@email_check_commands') }}/"+first_process+"/"+$("#domain").val(), function(data) {
            console.log(data);
            var k = Object.keys(data)[0];
            if (data[k] == "Success")
            {
                $("#"+k+"_result").html('<i style="color: #00DD00;" class="fa fa-check-circle"></i>');
            }
            else
            {
                errors.push(data[k]);
                $("#"+k+"_result").html('<i style="color: #DD0000;" class="fa fa-times-circle"></i>')
            }
            startProcess(listOfFunctions);
          }).fail(function() {
            window.location = "{{ action('DashboardController@index') }}";
        });
    }
    
    
    $("#check_domain").click(checkDomain);
    $("#check_domain_form").submit(function (event) {
        event.preventDefault();
        checkDomain();
    });
</script>
@endsection