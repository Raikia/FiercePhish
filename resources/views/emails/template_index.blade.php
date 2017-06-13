@extends('layouts.app', ['title' => 'Email Templates'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Email Templates</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-3 col-sm-3 col-xs-6">
    <div class="x_panel">
      <div class="x_title">
        <h2>List of Email Templates</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" method="post" id="addTemplateForm" action="{{ action('EmailController@addTemplate') }}">
          {{ csrf_field() }}
          <!--<div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">First Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last_name">Last Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="last_name" name="last_name" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label for="middle-name" class="control-label col-md-3 col-sm-3 col-xs-12">Email <span class="required">*</span></label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input id="email" class="form-control col-md-7 col-xs-12" required="required" type="text" name="email">
            </div>
          </div>

          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="add_target_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Create Target</button>
            </div>
          </div>-->
          <select class="form-control" size="25" id="listOfTemplates">
            @foreach ($allTemplates as $template)
              <option value="{{ $template->id }}">{{ $template->name }}</option>
            @endforeach
          </select>
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <input type="hidden" id="templateName" name="templateName" value="" />
              <button type="button" class="btn btn-success" data-toggle="modal" data-target=".createnew-modal-sm">Create New Template</button>
            </div>
          </div>
          
        </form>

      </div>
    </div>
  </div>
<div class="modal fade createnew-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title" id="myModalLabel2">Create New Template</h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal form-label-left" id="modalform">
        <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="template_name">Template Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="template_name" name="template_name" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" id="createTemplate_btn" class="btn btn-primary" style="margin-bottom: 5px;">Create Template</button>
      </div>

    </div>
  </div>
</div>
<div class="modal fade deletetemplate-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
        </button>
        <h4 class="modal-title" id="myModalLabel2">Are you sure?</h4>
      </div>
      <div class="modal-body">
        <h4>Delete Template?</h4>
        <p>This action cannot be undone!</p>
      </div>
      <div class="modal-footer">
        <form class="form-horizontal form-label-left" action="{{ action('EmailController@deleteTemplate') }}" method="post">
          {{ csrf_field() }}
          <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
          <button type="submit" id="deleteTemplate_btn" class="btn btn-danger" style="margin-bottom: 5px;">Delete Template</button>
          <input type="hidden" id="deleteId" name="deleteId" value="" />
        </form>
      </div>

    </div>
  </div>
</div>



  <div class="col-md-9 col-sm-9 col-xs-18">
    <div class="x_panel" id="templateCodeContent">
      <div class="x_title">
        <h2 id="loaded_template_name"><i class="fa fa-file-code-o"></i> Template Code</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <form class="form-horizontal form-label-left">
          <div class="form-group">
            <label class="control-label col-md-1 col-sm-1 col-xs-1" for="subject">Subject  <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group" style="width: 250px;">
                    <input type="text" id="subject" required="required" class="form-control" />
                </div>
                
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          <div class="form-group">
            <textarea style="width: 100%; height: 300px;" id="templateData"></textarea>
          </div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12">
                <p>You can use a variety of variables within a template to personalize them for each person. Below is a table of usable variables:</p>
                <table class="table table-striped table-bordered">
                  <thead>
                    <tr>
                      <th>Variable</th>
                      <th>Description</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach (App\EmailTemplate::$VARS as $var => $desc)
                      <tr>
                        <td>{{ $var }}</td>
                        <td>{{ $desc }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
                <p>If you use these variables in your template, the preview below will simulate Bill Smith ("bsmith@malicious.com") emailing John Doe ("john.doe@domain.com").</p>
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          <div class="ln_solid"></div>
        </form>
        <form class="form-horizontal form-label-left" id="templateForm" method="post" action="{{ action('EmailController@editTemplate') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <input type="hidden" name="subject" id="subject_real" value="" />
              <input type="hidden" id="template_id" name="template_id" />
              <input type="hidden" name="templateData" id="templateData_real" />
              <button type="button" id="delete_template_btn" class="btn btn-danger" data-toggle="modal" data-target=".deletetemplate-modal-sm">Delete Template</button>
              <button type="button" style="margin-left: 30px;" id="saveTemplateBtn" class="btn btn-success">Save Template</button>
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
      <div class="x_title">
        <h2><i class="fa fa-eye"></i> Template Preview</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <div class="outlook_window">
            <div class="outlook_header">
              <img style="height: 72px; width: 72px; float: left; display: block;" src="{{ asset('images/outlook_person.png') }}" />
              <div class="outlook_metadata" style="margin-left: 100px;">
                <div class="outlook_date">{{ date('D m/d/Y h:i a') }}</div>
                <div class="outlook_from">Bill Smith &lt;bsmith@malicious.com&gt;</div>
                <div class="outlook_subject" id="outlook_subject">Test Subject</div>
              </div>
              <div class="outlook_to"><span style="margin-right: 10px;">To:</span>John Doe &lt;john.doe@domain.com&gt;</div>
              <div class="outlook_message">
                <span id="outlook_content">
                  <p>Dear Bill</p>
                  <p>This is an example message because the template is currently blank!  If you write a message, this will populate with it.  Until then, this is a boring placeholder email.</p>
                  <p><b>Sincerely,</b></p>
                  <p><b>John Doe</b></p>
                </span>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
      </div>
    </div>
  </div>
</div>


@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    /* global CKEDITOR */
    
    function redraw_message() {
      var data = CKEDITOR.instances.templateData.getData();
      if (data.trim() == "")
      {
        data = "<p>Dear Bill</p><p>This is an example message because the template is currently blank!  If you write a message, this will populate with it.  Until then, this is a boring placeholder email.</p><p><b>Sincerely,</b></p><p><b>John Doe</b></p>";
      }
      $("#outlook_content").html(parseVariables(data));
    }
    function redraw_subject() {
      var data = $("#subject").val();
      if (data.trim() == "")
      {
        $("#outlook_subject").html('Test Subject');
      }
      else
      {
        $("#outlook_subject").html(parseVariables(data));
      }
    }
    
    CKEDITOR.replace('templateData',  {
      customConfig: '{{ asset("js/custom_ckeditor.js") }}'
    });
    
    $(document).ready(function() {
      CKEDITOR.instances.templateData.on('change', redraw_message);
      $("#subject").on('change', redraw_subject);
      @if ($currentTemplate->id !== null)
        var startingObj = {!! json_encode($currentTemplate) !!};
        loadAjaxData(startingObj);
      @else
        $("#templateCodeContent").block({
          message: "<h2>Select a template on the left</h2>",
          css: { 
            border: 'none', 
            padding: '15px', 
            backgroundColor: '#000', 
            '-webkit-border-radius': '10px', 
            '-moz-border-radius': '10px', 
            opacity: .5, 
            color: '#fff' 
          },
          overlayCSS: {
            cursor: 'auto',
            '-webkit-border-radius': '5px', 
            '-moz-border-radius': '5px',
            'border-radius': '5px'
          }
        });
      @endif
    });
    
    function parseVariables(emailTemplate) {
      @foreach (App\EmailTemplate::$DEFAULTS as $var => $val)
        emailTemplate = emailTemplate.split("{{ $var }}").join("{{ $val }}")
      @endforeach
      return emailTemplate;
    }
    

    function createTemplateHandler() {
      $("#templateName").val($("#template_name").val());
      $("#addTemplateForm").submit();
    }
    $("#modalform").submit(function(event) {
      event.preventDefault();
      createTemplateHandler();
    });
    $("#createTemplate_btn").click(createTemplateHandler);
    
    
    
    function loadAjaxData(data) {
      $("#templateCodeContent").unblock();
      $("#subject").val(data.subject);
      $("#template_id").val(data.id);
      $("#deleteId").val(data.id);
      CKEDITOR.instances.templateData.setData(data.template);
      $("#loaded_template_name").html("<i class=\"fa fa-file-code-o\"></i> Template Code for \""+data.name+"\"");
      $("#loading_modal").modal('hide');
      redraw_message();
      redraw_subject();
    }
    
    
    $("#listOfTemplates").on('change', function() {
      var idToFind = $("#listOfTemplates").val();
      $("#loading_modal").modal({
        show: true,
        keyboard: false,
        backdrop: 'static'
      });
      
      $.get("{{ action('AjaxController@get_emailtemplate_info') }}/"+idToFind, function(data) {
        loadAjaxData(data);
      }).fail(function() {
        window.location = "{{ action('DashboardController@index') }}";
      });
    });
    
    $("#saveTemplateBtn").click(function() {
      $("#subject_real").val($("#subject").val());
      $("#templateData_real").val(CKEDITOR.instances.templateData.getData());
      $("#templateForm").submit();
    })
    
</script>
@endsection