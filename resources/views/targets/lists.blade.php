@extends('layouts.app', ['title' => 'All Target Lists'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>List of Lists</h3>
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
                      <th>Name</th>
                      <th># of Users</th>
                      <th>Notes</th>
                  </tr>
              </thead>
              <tbody>
                  @if (count($targetLists) > 0)
                      @foreach ($targetLists as $list)
                          <tr>
                              <td><a href="{{ action('TargetsController@targetlists_details', ['id' => $list->id]) }}">{{ str_limit($list->name,200) }}</a></td>
                              <td>{{ number_format($list->users()->count()) }}</td>
                              <td><a href="#" class="editnotes" data-type="text" data-pk="{{ $list->id }}" data-url="{{ action('AjaxController@edit_targetlist_notes') }}" data-title="Enter note">{{ $list->notes }}</a></td>
                          </tr>
                      @endforeach
                  @else
                      <tr>
                          <td colspan="4" style="text-align: center;">No Lists Yet</td>
                      </tr>
                  @endif
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
        <h2><i class="fa fa-plus"></i> Add a List</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" method="post" action="{{ action('TargetsController@addList') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="name" name="name" required="required" class="form-control col-md-7 col-xs-12">
            </div>
          </div>

          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="submit" class="btn btn-success">Create List</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>

<!--
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
  </div>-->
</div>

@endsection

@section('footer')
<script type="text/javascript">
    /* global $ */
    
    
    var dt = $(".datatable").DataTable();
    
    $(".editnotes").editable();
    
    $(".editnotes").on('save', function() {
        setTimeout(function() {
            dt.rows().invalidate();
        }, 500);
    });
</script>
@endsection