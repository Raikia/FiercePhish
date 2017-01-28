@extends('layouts.app', ['title' => 'Assign Targets to List'])

@section('content')

<div class="page-title">
  <div class="title_left">
      <h3>Assign Targets to Lists</h3>
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
    <div class="x_panel" style="overflow: auto;">
      <div class="x_title">
        <h2><i class="fa fa-plus"></i> Selected Targets</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
        <table class="table" id="selectedTable">
          <thead>
            <tr>
              <th>#</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
            </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="4" style="text-align: center;">None Selected</td>
              </tr>
            </tbody>
        </table>

      </div>
    </div>
  </div>


  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-floppy-o"></i> 
          Add Selection To List
        </h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" id="addToListForm" method="post" action="{{ action('TargetsController@assignToLists') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="listSelection">Select List  <span class="required">*</span>
            </label>
            <div class="col-md-9 col-sm-9 col-xs-12">
                <input type="hidden" name="listSelection" value="{{ $selectedList->id }}" />
                <input type="hidden" name="type" value="edit" />
                <input type="text" class="form-control" id="listSelection" value="{{ $selectedList->name}} - {{ $selectedList->users()->count() }} users" readonly />
            </div>
          </div>
          
          
          
          <input type="hidden" id="rowsToAdd" name="rowsToAdd" value="" />
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                <button type="submit" class="btn btn-success">Edit List</button>
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
    var selected = [];
    var selectedData = [];
    /*
    var dt = $(".datatable").DataTable({
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_list') }}/"+{{ $selectedList->id }},
        type: "POST"
      },
      columnDefs: [{ targets: 'no-sort', orderable: false}],
      'rowCallback': function( row, data ) {
            if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                $(row).addClass('selected');
            }
        }
    });*/
    var a = '';
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Target Users Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_list') }}/"+{{ $selectedList->id }},
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
                                                                          }, orderable: false, searchable: false, sortable: false},
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
      ],
      'rowCallback': function( row, data ) {
            if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
                $(row).addClass('selected');
            }
        }
    });
    
    
    $('.datatable tbody').on('click', 'tr', function (data) {
        var id = this.id;
        var index = $.inArray(id, selected);
        if ( index === -1 ) {
            selected.push( id );
            selectedData[id.split('_')[1]] = [data.currentTarget.cells[0].innerHTML, data.currentTarget.cells[1].innerHTML, data.currentTarget.cells[2].innerHTML];
            updateSelectedTable();
        } else {
            selected.splice( index, 1 );
            delete selectedData[id.split('_')[1]];
            updateSelectedTable();
        }
 
        $(this).toggleClass('selected');
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
    
    
    $("#addToListForm").submit(function(event) {
      var ids = selected;
      var input_ids = [];
      for (var x=0; x< ids.length; ++x)
      {
        input_ids.push(ids[x].replace("row_",""));
      }
      $("#rowsToAdd").val(input_ids.join());
      return true;
    });
    
    
    function updateSelectedTable()
    {
        var total_add = '';
        var num = 1;
        var keys = Object.keys(selectedData);
        for (var x=0; x < keys.length; ++x)
        {
            total_add += '<tr><td>'+num+'</td><td>'+selectedData[keys[x]][0]+'</td><td>'+selectedData[keys[x]][1]+'</td><td>'+selectedData[keys[x]][2]+'</td></tr>';
            ++num;
        }
        if (keys.length == 0)
            total_add += '<tr><td colspan="4" style="text-align: center;">None Selected</td></tr>';
        $("#selectedTable tbody").html(total_add);
    }
    
    CURRENT_URL = "{{ action('TargetsController@targetlists_index') }}";
</script>
@endsection