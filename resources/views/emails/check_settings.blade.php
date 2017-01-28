@extends('layouts.app', ['title' => 'Check email settings'])

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
          <span id="searchResults">
              
          </span>
      </div>
    </div>
  </div>
</div>

<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
        <div class="x_title">
        <h2><i class="fa fa-info-circle"></i> Required DNS Settings</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <p>To properly bypass spam filters, you should buy a domain name similar to your target's domain (or something that looks legitimate) and send emails from that domain name.
          If you try to spoof an email from a domain owned by someone else, there is a much higher chance of being caught by spam filters. If you actually own the domain you are sending emails 
          from, you will bypass most spam filters since it is actually a &quot;legitimate&quot; email.</p>
          <p>The records below show the DNS entries you <i>should</i> set if you own the domain.  For the examples, we will use "domain.com" as the domain you purchased (or input a domain above and it will change).</p>
          <table class="table" style="width: 950px; margin-left: auto; margin-right: auto;">
              <thead>
                  <tr>
                      <th>Record Type</th>
                      <th>Host</th>
                      <th>Target</th>
                  </tr>
              </thead>
              <tbody>
                  <tr>
                      <td>A Record</td>
                      <td>@</td>
                      <td>{{ $server_ip }}</td>
                  </tr>
                  <tr>
                      <td>A Record</td>
                      <td>www</td>
                      <td>{{ $server_ip }}</td>
                  </tr>
                  <tr>
                      <td>A Record</td>
                      <td>mail</td>
                      <td>{{ $server_ip }}</td>
                  </tr>
                  <tr>
                      <td>MX Record (or MXE Record)</td>
                      <td>N/A</td>
                      <td>mail.<span style="line-height: normal" class="changeDomain">domain.com.</span> (or {{ $server_ip }} if MXE)</td>
                  </tr>
                  <tr>
                      <td>TXT Record</td>
                      <td>@</td>
                      <td>v=spf1 a a:mail.<span style="line-height: normal" class="changeDomain">domain.com</span> a:<span style="line-height: normal" class="changeDomain">domain.com</span> ip4:{{ $server_ip }} ~all</td>
                  </tr>
                  <tr>
                      <td>TXT Record</td>
                      <td>_dmarc</td>
                      <td>v=DMARC1; p=none</td>
                  </tr>
                  <tr>
                      <td>TXT Record</td>
                      <td>mail._domainkey</td>
                      <td><b>DKIM is generated, look it up</b></td>
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
    
    function checkDomain() {
        $(".ajax_results").html('<i class="fa fa-refresh fa-spin"></i>');
        $("#searchResults").html('');
        var listOfFunctions = [];
        var tableRows = $("#settingsCheckTable tr td:nth-child(2)");
        for (var x=0; x < tableRows.length; ++x)
        {
            listOfFunctions.push(tableRows[x].id.replace("_result",""));
        }
        if ($("#domain").val() != '')
            $(".changeDomain").html($("#domain").val());
        errors = [];
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
            var k = Object.keys(data)[0];
            if (data[data.command])
            {
                errors.push(data['message']);
                $("#"+k+"_result").html('<i style="color: #00DD00;" class="fa fa-check-circle"></i>');
            }
            else
            {
                errors.push(data['message']);
                $("#"+k+"_result").html('<i style="color: #DD0000;" class="fa fa-times-circle"></i>')
            }
            drawErrors();
            startProcess(listOfFunctions);
          }).fail(function() {
            window.location = "{{ action('DashboardController@index') }}";
        });
    }
    
    function drawErrors()
    {
        var errStr = "<pre>Results:<br /><br />";
        var tableKeys = {!! json_encode(array_values($settingsCheck)) !!};
        if (errors.length == 0)
        {
            errStr = '* All checks passed!';
        }
        else
        {
            for (var x=0; x<tableKeys.length; ++x)
            {
                if (x < errors.length)
                    errStr += "&nbsp;&nbsp;&nbsp;&nbsp;* " + tableKeys[x] + " - " + errors[x]+"<br />";
            }
        }
        errStr += "</pre>";
        $("#searchResults").html(errStr);
    }
    
    
    $("#check_domain").click(checkDomain);
    $("#check_domain_form").submit(function (event) {
        event.preventDefault();
        checkDomain();
    });
</script>
@endsection