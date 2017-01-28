@extends('layouts.app', ['title' => 'All Target Users'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>List of Targets</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel" style="overflow: auto;">
      <div class="x_content">
          <table class="table table-striped table-bordered datatable">
              <thead>
                  <tr>
                      <th>First name</th>
                      <th>Last name</th>
                      <th>Email</th>
                      <th class="no-sort">List Membership</th>
                      <th>Notes</th>
                  </tr>
              </thead>
              <tbody>
              </tbody>
          </table>
      </div>
    </div>
  </div>
</div>


<!----------------- -->

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-plus"></i> Add a Target</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" method="post" action="{{ action('TargetsController@addTarget') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">First Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="last_name">Last Name
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="last_name" name="last_name" class="form-control col-md-7 col-xs-12">
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
          </div>

        </form>

      </div>
    </div>
  </div>


  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-download"></i> Import Targets</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" enctype="multipart/form-data" method="post" action="{{ action('TargetsController@importTargets') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="import_file">Import File  <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <span class="input-group-btn">
                        <label class="btn btn-primary"><i class="fa fa-file-o"></i><input type="file" name="import_file" id="import_file" style="visibility: hidden; position:absolute;" /></label>
                    </span>
                    <input type="text" id="selectedFile" class="form-control" readonly />
                </div>
                
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="first_name">CSV Format 
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <p style="margin-top: 8px;">First Name, Last Name, Email, [Notes]</p>
                </div>
              <!--<label class="btn btn-default btn-file">
                  Browse <input type="file" style="display: none;" />
              </label>-->
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">-->
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="import_file_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Import Targets</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>

@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    $("#add_target_clear_btn").click(function() {
        $("#first_name").val('');
        $("#last_name").val('');
        $("#email").val('');
    });
    
    $("#import_file").change(function() {
        $("#selectedFile").val($(this).val());
    });
    
    $("#import_file_clear_btn").click(function() {
        $("#import_file").val('');
        $("#selectedFile").val('');
    });
    
    /*var dt = $(".datatable").DataTable({
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_list') }}",
        type: "POST"
      },
      columnDefs: [{ targets: 'no-sort', orderable: false}]
    });*/
    
    
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Target Users Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_list') }}",
        type: "POST"
      },
      columns: [
        { data: 'first_name', name: 'first_name'},
        { data: 'last_name', name: 'last_name'},
        { data: 'email', name: 'email'},
        { data: 'list_of_membership', name: 'list_of_membership', render: function(data, type, row) {
                                                                            var parts = data.split("-=|=-");
                                                                            var ret = '<ul style="margin-bottom: 0px; padding-left: 25px;">';
                                                                            for (var x=0; x < parts.length; ++x)
                                                                            {
                                                                              ret += '<li>' + parts[x] + '</li>';
                                                                            }
                                                                            ret += '</ul>';
                                                                            if (parts.length == 0 || (parts.length == 1 && parts[0] == ""))
                                                                            {
                                                                              ret = 'None';
                                                                            }
                                                                            return ret;
                                                                          }, orderable: false, searchable: false},
        { data: 'notes', name: 'notes', render: function(data, type, row) {
                                                  var emptyClass = ' editable-empty';
                                                  var noteValue = 'Empty';
                                                  if (data != "")
                                                  {
                                                    emptyClass = "";
                                                    noteValue = data;
                                                  }
                                                  return '<a href="#" class="editnotes' + emptyClass + '" data-type="text" data-pk="' + row.id + '" data-url="{{ action('AjaxController@edit_targetuser_notes') }}" data-title="Enter note">' + noteValue + '</a>';
                                                }
        }
      ]
    });
    
    
    
    $(".datatable").editable({
      selector: 'tr td:nth-child(5) a',
      emptytext: 'Empty'
    });
    $(".editnotes").on('save', function() {
        setTimeout(function() {
            dt.rows().invalidate();
        }, 500);
    });
</script>
@endsection