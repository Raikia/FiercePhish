@extends('layouts.app', ['title' => 'Hosted File Summary'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Hosted File Summary</h3>
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
                      <th>Original File Name</th>
                      <th>File Path</th>
                      <th>Mime Type</th>
                      <th>Action</th>
                      <th>Views</th>
                      <th>Created Date</th>
                  </tr>
              </thead>
              <tbody>
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
    
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Files Found"
      }
    });
    
</script>
@endsection