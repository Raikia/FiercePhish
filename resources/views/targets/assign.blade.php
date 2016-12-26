@extends('layouts.app', ['title' => 'Assign Targets to List'])

@section('content')

<div class="page-title">
  <div class="title_left">
    @if ($selectedList->id === null)
      <h3>Assign Targets to Lists</h3>
    @else 
      <h3>Edit "{{ $selectedList->name }}"</h3>
    @endif
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
                      <th>List Membership</th>
                      <th>Notes</th>
                  </tr>
              </thead>
              <tbody>
                  @if (count($targetUsers) > 0)
                      @foreach ($targetUsers as $user)
                          <tr id="row_{{ $user->id }}">
                              <td>{{ str_limit($user->first_name,50) }}</td>
                              <td>{{ str_limit($user->last_name,50) }}</td>
                              <td>{{ str_limit($user->email,50) }}</td>
                              <td>
                                  @if (count($user->lists) > 0)
                                      <ul>
                                          @foreach ($user->lists as $l)
                                              <li>{{ $l->name }}</li>
                                          @endforeach
                                      </ul>
                                  @else
                                      None
                                  @endif
                              </td>
                              <td>{{ $user->notes }}</td>
                          </tr>
                      @endforeach
                  @else
                      <tr>
                          <td colspan="4" style="text-align: center;">No Targets Yet</td>
                      </tr>
                  @endif
              </tbody>
          </table>
          <input type="button" id="selectAllOnPage_btn" value="Select All on Page" />
          <input type="button" id="selectAll_btn" value="Select All" style="margin-left: 30px;" />
          <input type="button" id="deselectAll_btn" value="Deselect All" style="margin-left: 30px;" />
          <br />
          <br />
          <input type="number" placeholder="X amount" min="1" max="{{ count($targetUsers) }}" id="numToSelect" style="padding-left: 5px; width: 80px;" /> 
          <input type="button" id="numToSelect_btn" value="Select X Amount Randomly" style="margin-left: 10px; margin-right: 10px;" />
          Only unassigned targets: <input type="checkbox" id="unusedOnly" value="unassigned_only" style="margin-right: 10px;" />
          <span id="randomSelectError" style="color: #DD0000;"></span>
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
        <h2><i class="fa fa-download"></i> 
        @if ($selectedList->id === null)
          Add Selection To List
        @else 
          Edit List 
        @endif
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
              @if ($selectedList->id === null)
                      <select id="listSelection" name="listSelection" class="select2_single form-control" style="width: 300px;">
                        <option></option>
                        @foreach ($targetLists as $list)
                          <option value="{{ $list->id }}">{{ $list->name }} - {{ count($list->users) }} users</option>
                        @endforeach
                      </select>
                      <input type="hidden" name="type" value="add" />
              @else
                <input type="hidden" name="listSelection" value="{{ $selectedList->id }}" />
                <input type="hidden" name="type" value="edit" />
                <input type="text" class="form-control" id="listSelection" value="{{ $selectedList->name}} - {{ count($selectedList->users) }} users" readonly />
              @endif
            </div>
          </div>
          
          
          
          <input type="hidden" id="rowsToAdd" name="rowsToAdd" value="" />
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              @if ($selectedList->id === null)
                <button type="submit" class="btn btn-success">Add Selection To List</button>
              @else 
                <button type="submit" class="btn btn-success">Edit List</button>
              @endif
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
    
    var dt = $(".datatable").DataTable({
        select: 'multi'
    });
    
    $("#selectAllOnPage_btn").click(function() {
        dt.rows({ page:'current' }).select();
    });
    
    $("#selectAll_btn").click(function() {
        dt.rows().select();
    });
    
    $("#deselectAll_btn").click(function() {
        dt.rows().deselect();
    });
    
    $("#numToSelect_btn").click(function() {
        var max = Math.min($("#numToSelect").val(), dt.rows({selected: false}).count());
        if (max == 0)
            setError("No rows to select")
        else if ($("#numToSelect").val() != max)
            setError("Too many rows requested, selecting all");
        else if ($("#numToSelect").val() == dt.rows({selected: false}).count())
            setError("All rows being selected");
        else
            setError("");
        if (!$.isNumeric(max))
        {
            $("#numToSelect").val('');
            setError("Invalid number");
            return;
        }
        if ($("#unusedOnly").prop('checked'))
        {
            var unselected_ones = dt.rows({selected: false}).eq(0).filter( function(rowIdx) {
                    return dt.cell(rowIdx, 3).data() == "None" ? true : false;
                });
            max = Math.min(max, unselected_ones.length);
            if (max == 0)
                setError("No unassigned rows remaining");
            for (var x=0; x<max; ++x)
            {
                var unselected_ones = dt.rows({selected: false}).eq(0).filter( function(rowIdx) {
                    return dt.cell(rowIdx, 3).data() == "None" ? true : false;
                });
                var random_row = Math.floor(Math.random() * (unselected_ones.count()));
                dt.row(unselected_ones[random_row], {selected: false}).select();
            }
        }
        else
        {
            
            for (var x=0; x<max; ++x)
            {
                var unselected_ones = dt.rows({selected: false}).indexes();
                var random_row = Math.floor(Math.random() * (unselected_ones.count()));
                dt.row(unselected_ones[random_row], {selected: false}).select();
            }
        }
    });
    
    function selectionHandler( e, dt, type,indexes) {
      var total_add = "";
      var data = dt.rows({selected: true}).data();
      var num = 1;
      for (var x=0; x < data.length; ++x)
      {
        total_add += '<tr><td>'+num+'</td><td>'+data[x][0]+'</td><td>'+data[x][1]+'</td><td>'+data[x][2]+'</td></tr>';
        ++num;
      }
      if (data.length == 0)
        total_add += '<tr><td colspan="4" style="text-align: center;">None Selected</td></tr>';
      $("#selectedTable tbody").html(total_add);
    }
    
    dt.on('select', selectionHandler);
    dt.on('deselect', selectionHandler);
    
    function setError(str) {
        $("#randomSelectError").html(str);
    }
    
    @if ($selectedList->id === null)
      $(".select2_single").select2({
        placeholder: "Select a list",
        allowClear: true
      });
    @endif

    
    
    $("#addToListForm").submit(function(event) {
      var ids = dt.rows({selected: true}).ids();
      var input_ids = [];
      for (var x=0; x< ids.length; ++x)
      {
        input_ids.push(ids[x].replace("row_",""));
      }
      $("#rowsToAdd").val(input_ids.join());
      return true;
    });
    
    
    $(document).ready(function() {
      @foreach ($selectedList->users as $u)
        dt.row('#row_{{ $u->id }}').select();
      @endforeach
      
    });
</script>
@endsection