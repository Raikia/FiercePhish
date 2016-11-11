@extends('layouts.app')

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>List of Targets</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
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
                          <tr>
                              <td>{{ $user->first_name }}</td>
                              <td>{{ $user->last_name }}</td>
                              <td>{{ $user->email }}</td>
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
          <input type="number" placeholder="X amount" min="1" id="numToSelect" style="padding-left: 5px; width: 80px;" /> 
          <input type="button" id="numToSelect_btn" value="Select X Amount Randomly" style="margin-left: 10px; margin-right: 10px;" />
          Only unassigned targets: <input type="checkbox" id="unusedOnly" value="unassigned_only" />
      </div>
    </div>
  </div>
</div>


<!----------------- -->
<!--
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
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">--
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
              <!--<input type="file" id="first_name" name="first_name" required="required" class="form-control col-md-7 col-xs-12">--
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" id="import_file_clear_btn" class="btn btn-primary">Clear</button>
              <button type="submit" class="btn btn-success">Create Target</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
-->
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
        var max = $("#numToSelect").val()
        if (!$.isNumeric(max))
        {
            $("#numToSelect").val('');
            return;
        }
        if ($("#unusedOnly").prop('checked'))
        {
            var unassigned_ones = dt.column(3).data().filter(function(val, index, api) {
                if (val == "None")
                    return true;
                return false;
            });
            
            for (var x=0; x < max; ++x)
            {
                var random_row = Math.floor(Math.random() * (unassigned_ones.length));
                dt.row(random_row).select();
            }
        }
        else
        {
            for (var x = 0; x < max; ++x)
            {
                var random_row = Math.floor(Math.random() * (dt.rows().data().length));
                dt.row(random_row).select();
            }
        }
    });
</script>
@endsection