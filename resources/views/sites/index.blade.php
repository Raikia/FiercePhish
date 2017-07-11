@extends('layouts.app', ['title' => 'Hosted Files'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted Sites</h3>
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
                    <th>Site Name</th>
                    <th>Package Name</th>
                    <th>Folder</th>
                    <th># Files</th>
                    <th>Total Views</th>
                    <th>Total Credentials</th>
                    <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
                @foreach ($allsites as $site)
                <tr>
                  <td><a href="{{ action('HostedSiteController@siteview', ['id' => $site->id]) }}">{{ $site->name }}</a></td>
                  <td><a href="{{ $site->package_url }}">{{ $site->package_name }}</a> by <a href="mailto:{{ $site->package_email }}">{{ $site->package_author }}</a></td>
                  <td>{{ str_replace('//', '/', '/'.$site->route.'/') }}</td>
                  <td>{{ $site->files()->count() }}</td>
                  <td>{{ $site->files()->withCount('views')->get()->sum('views_count') }}</td>
                  <td>{{ count($site->credentials()) }}</td>
                  <td>{{ \App\Libraries\DateHelper::readable($site->created_at) }}</td>
                </tr>
                @endforeach
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
        <h2><i class="fa fa-plus"></i> Host a Site</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" enctype="multipart/form-data" method="post" action="{{ action('HostedSiteController@addsite') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Name <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <input type="text" class="form-control" name="name" />
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Folder Path </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
              <input type="text" id="path" name="path" class="form-control col-md-7 col-xs-12" placeholder="example/location/" value="{{ old('name') }}">
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Root Path <span class="required">*</span></label>
            <div class="col-md-9 col-sm-9 col-xs-12" style="margin-top:8px;" id="selected_path">
              <span id="currentPath">{{ \Request::root() }}/</span>
            </div>
          </div>
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12">Select Site Package <span class="required">*</span></label>
            <div class="col-md-9 col-sm-9 col-xs-12">
              <label class="btn btn-primary" for="attachment">
                <input type="file" required="required" id="attachment" name="attachment" style="display: none;" onchange="$('#upload-file-info').html($(this).val())">
                Browse...
              </label>
              <span class="label label-info" id="upload-file-info"></span>
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="submit" class="btn btn-success">Upload Site</button>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>


  <div class="col-md-6 col-sm-6 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2><i class="fa fa-trash"></i> Delete Site</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">

        <form class="form-horizontal form-label-left" id="deletesiteForm" method="post" action="{{ action('HostedSiteController@deletesite') }}">
          {{ csrf_field() }}
          <div class="form-group">
            <label class="control-label col-md-3 col-sm-3 col-xs-12" for="site">Select Site <span class="required">*</span>
            </label>
            <div class="col-md-6 col-sm-6 col-xs-12">
                <div class="input-group">
                    <select class="form-control" style="width: 200px;" id="site" name="site">
                        <option></option>
                        @foreach ($allsites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }} - {{ $site->files()->count() }} files</option>
                        @endforeach
                    </select>
                </div>
            </div>
          </div>
          
          <div class="ln_solid"></div>
          <div class="form-group">
            <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
              <button type="button" class="btn btn-success" data-toggle="modal" data-target=".deletefile-modal-sm">Delete Site</button>
              <div class="modal fade deletefile-modal-sm" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                      <div class="modal-content">

                        <div class="modal-header">
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span>
                          </button>
                          <h4 class="modal-title" id="myModalLabel2">Are you sure?</h4>
                        </div>
                        <div class="modal-body">
                          <h4>Delete Site?</h4>
                          <p>This will delete all logs related to this site and cannot be undone!  I recommend you just disable the site instead!</p>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                          <button type="button" id="deletesite_btn" class="btn btn-danger" style="margin-bottom: 5px;">Delete Site</button>
                        </div>

                      </div>
                    </div>
                  </div>
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
      language: {
        "emptyTable": "No Sites Found"
      }
    });
    
    $("#deletesite_btn").click(function() {
        $("#deletesiteForm").submit();
    });
    
    var url="{{ \Request::root() }}/";
    
    $("#path").keyup(function() {
        var oldVal = $("#path").val();
        oldVal = oldVal.replace(/^\/+/g, '').replace(/[^0-9a-zA-Z_\.%\/]/g, '');
        $("#path").val(oldVal);
        updatePath();
    });
    
    function updatePath()
    {
        var newUrl = url;
        newUrl += $("#path").val();
        $("#currentPath").html(newUrl);
    }
    
</script>
@endsection