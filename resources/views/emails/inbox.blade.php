@extends('layouts.app', ['title' => 'Inbox'])

@section('content')
    <div class="page-title">
      <div class="title_left">
        <h3>Inbox</h3>
      </div>
    </div>

    <div class="clearfix"></div>

    <div class="row">
      <div class="col-md-12">
        <div class="x_panel">
          <div class="x_title">
            <h2>Catch-all Inbox</h2>
            <div class="clearfix"></div>
          </div>
          <div class="x_content">
            <div class="row">
              <div class="col-sm-3 mail_list_column">
                <a href="{{ action('EmailController@send_simple_index') }}" id="compose" class="btn btn-sm btn-success btn-block" type="button" style="background: #FF4800; border: 1px solid #D9320B;">COMPOSE</a>
                <div style="border-bottom: 1px solid #DBDBDB; margin-bottom: 10px;"></div>
                <div style="max-height: 580px; overflow-y: auto;" id="mailList">
                  <a href="#">
                    <div class="mail_list" style="padding-bottom: 10px;">
                      <div class="center" style="text-align: center;">
                        Loading Messages...
                      </div>
                    </div>
                  </a>
                </div>
              </div>
              <!-- /MAIL LIST -->

              <!-- CONTENT MAIL -->
              <div class="col-sm-9 mail_view" id="mailContent" style="min-height: 580px;">
                
              </div>
              <!-- /CONTENT MAIL -->
            </div>
          </div>
        </div>
      </div>
    </div>


@endsection



@section('footer')

<script type="text/javascript">
  /* global $ */
  /* global bootbox */
  
  $(document).ready(function () {
    checkMail();
    setInterval(checkMail, 2000);
    $(document).on('click', ".openmail", function (data) {
      openMail(data.currentTarget.id.split("_")[1]);
      checkMail();
    });
    $(document).on('click', '.dltbtn', deleteMessage);
  });
  
  $("#mailContent").block({
    message: "<h2>Select an email on the left</h2>",
    css: { 
      border: 'none', 
      padding: '15px', 
      backgroundColor: '#FFF', 
      '-webkit-border-radius': '10px', 
      '-moz-border-radius': '10px', 
      opacity: .9, 
      color: '#000' 
    },
    overlayCSS: {
      cursor: 'auto',
      '-webkit-border-radius': '5px', 
      '-moz-border-radius': '5px',
      'border-radius': '5px'
    }
  });
  
  function checkMail()
  {
    $.get("{{ action('AjaxController@get_inbox_messages') }}", function(raw) {
      var data = raw.data;
      var str = "";
      for (var x=0; x < data.length; ++x)
      {
        str += '<a class="openmail single_pointer" id="mail_'+data[x].id+'">';
        str += '<div class="mail_list">';
        str += '<div class="left">';
        if (data[x].seen == 0)
          str += '<i class="fa fa-circle"></i>';
        if (data[x].replied == 1)
          str += '<i class="fa fa-mail-reply"></i>';
        if (data[x].forwarded == 1)
          str += '<i class="fa fa-share"></i>';
        if (data[x].attachment_count.length > 0)
          str += '<i class="fa fa-paperclip"></i>';
        str += '<i class="fa"></i></div>';
        str += '<div class="right">';
        str += '<h3>'+data[x].sender_name+' <small>'+data[x].received_date+'</small></h3>';
        var text = data[x].sub_msg.split("\n")[0].substr(0,50);
        if (text != data[x].sub_msg)
          text += "...";
        var subject = data[x].subject.substr(0, 42);
        if (subject != data[x].subject)
          subject += "...";
        str += '<p><span class="badge" style="margin-bottom: 2px;">Subject</span> '+subject+'<br />'+text+'</p>';
        str += '</div>';
        str += '</div>';
        str += '</a>';
      }
      if (data.length == 0)
      {
        str = '<a><div class="mail_list" style="padding-bottom: 10px;"><div class="center" style="text-align: center;">No Messages Found</div></div></a>';
      }
      $("#mailList").html(str);
    });
  }
  
  function openMail(mail_id)
  {
    $("#mailContent").unblock();
    $("#loading_modal").modal({
        show: true,
        keyboard: false,
        backdrop: 'static'
      });
    $.get("{{ action('AjaxController@get_inbox_messages') }}/"+mail_id, function(raw) {
      $("#loading_modal").modal('hide');
      var data = raw['data'];
      var str = '';
      //str += '<div class="col-sm-9 mail_view">';
      str += '<div class="inbox-body">';
      str += '<div class="mail_heading row">';
      str += '<div class="col-md-8">';
      str += '<div class="btn-group">';
      str += '<a href="{{ action("EmailController@send_simple_index") }}/'+data.id+'" class="btn btn-sm btn-primary"><i class="fa fa-reply"></i> Reply</a>';
      str += '<a href="{{ action("EmailController@send_simple_index") }}/'+data.id+'/fwd" class="btn btn-sm btn-default" data-placement="top" data-toggle="tooltip" data-original-title="Forward"><i class="fa fa-share"></i></a>';
      str += '<button class="btn btn-sm btn-default dltbtn" id="delete_'+data.id+'" type="button" data-placement="top" data-toggle="tooltip" data-original-title="Trash"><i class="fa fa-trash-o"></i></button>';
      str += '</div>';
      str += '</div>';
      str += '<div class="col-md-4 text-right">';
      str += '<p class="date"> ' + data.received_date +'</p>';
      str += '</div>';
      str += '<div class="col-md-12">';
      str += '<h4><span class="badge" style="margin-bottom: 2px;">Subject</span> ' + data.subject + '</h4>';
      str += '</div>';
      str += '</div>';
      str += '<div class="sender-info" style="padding-bottom: 5px; border-bottom: 1px dashed #DDD;">';
      str += '<div class="row">';
      str += '<div class="col-md-12">';
      str += '<strong>' + data.sender_name+ '</strong> ';
      str += '<span>('+data.sender_email+')</span> ';
      if (data.sender_email != data.replyto_email || data.sender_name != data.replyto_name)
        str += '(reply to: ' + data.replyto_name + ' &lt;'+data.replyto_email+'&gt;) ';
      str += 'to <strong>'+data.receiver_name+' ('+data.receiver_email+')</strong>';
      str += '<a class="sender-dropdown"><i class="fa fa-chevron-down"></i></a>';
      str += '</div>';
      str += '</div>';
      str += '</div>';
      str += '<div class="view-mail" style="padding-top: 10px; font-family: monospace;"><p>';
      var messageLines = data.message.replace("\r","").split("\n");//.filter(function(n) { return n.trim() != ""; });
      for (var x=0; x<messageLines.length; ++x)
        str += '' + messageLines[x] + '<br />';
      str += '</p></div>';
      if (data.attachments.length != 0)
      {
        var plural = 's';
        if (data.attachments.length == 1)
          plural = '';
        str += '<div class="attachment" style="border-top: 1px dashed #DDD; padding-top: 10px;">';
        str += '<p>';
        str += '<span><i class="fa fa-paperclip"></i> '+data.attachments.length+' attachment'+plural+' </span>';
        str += '</p>';
        str += '<ul>';
        
        for (var x=0; x<data.attachments.length; ++x)
        {
          str += '<li>';
          str += '<a href="#" class="atch-thumb">';
          str += '<img src="{{ asset("images/mail_attachment.png") }}" alt="img" />';
          str += '</a>';
          str += '';
          str += '<div class="file-name">Filename:  ';
          str += data.attachments[x].name;
          str += '</div>';
          str += '';
          str += '';
          str += '<div class="links">';
          str += '<a href="{{ action("EmailController@inbox_download_attachment") }}/'+data.attachments[x].id+'">Download</a>';
          str += '</div>';
          str += '</li>';
        }
        str += '</ul>';
        str += '</div>';
      }
      str += '<div class="btn-group">';
      str += '<a href="{{ action("EmailController@send_simple_index") }}/'+data.id+'" class="btn btn-sm btn-primary"><i class="fa fa-reply"></i> Reply</a>';
      str += '<a href="{{ action("EmailController@send_simple_index") }}/'+data.id+'/fwd" class="btn btn-sm btn-default" data-placement="top" data-toggle="tooltip" data-original-title="Forward"><i class="fa fa-share"></i></a>';
      str += '<button class="btn btn-sm btn-default dltbtn" id="delete_'+data.id+'" type="button" data-placement="top" data-toggle="tooltip" data-original-title="Trash"><i class="fa fa-trash-o"></i></button>';
      str += '</div>';
      str += '</div>';
      str += '';
     // str += '</div>';
      
      $("#mailContent").html(str);
    });
  }
  
  
  function deleteMessage(data)
  {
    var id_to_delete = data.currentTarget.id.split("_")[1];
    bootbox.confirm("Are you sure you want to delete this email?", function(result) {
      if (result) {
        $.get("{{ action('AjaxController@delete_inbox_message') }}/"+id_to_delete, function (raw) {
          $("#mailContent").html('');
          checkMail();
          $("#mailContent").block({
            message: "<h2>Email deleted successfully!<br />Select an email on the left</h2>",
            css: { 
              border: 'none', 
              padding: '15px', 
              backgroundColor: '#FFF', 
              '-webkit-border-radius': '10px', 
              '-moz-border-radius': '10px', 
              opacity: .9, 
              color: '#000' 
            },
            overlayCSS: {
              cursor: 'auto',
              '-webkit-border-radius': '5px', 
              '-moz-border-radius': '5px',
              'border-radius': '5px'
            }
          });
        });
      }
    });
  }
</script>


@endsection
