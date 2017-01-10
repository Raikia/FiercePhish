@extends('layouts.app', ['title' => 'Email Log'])

@section('content')
<div class="page-title">
  <div class="title_left">
    <h3>Email Log</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
      <div class="x_title">
        <h2>List of All Emails</h2>
        <div class="clearfix"></div>
      </div>
      <div class="x_content">
          <table class="table table-hover table-striped table-bordered datatable pointer" id="mainEmailLogTable">
            <thead>
              <tr>
                <th>Receiver Name</th>
                <th>Receiver Email</th>
                <th>Sender Name</th>
                <th>Sender Email</th>
                <th>Subject</th>
                <th>UUID</th>
                <th>Status</th>
                <th class="no-sort">Campaign</th>
                <th>Created At</th>
                <th>Updated At</th>
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
  $(document).on('click', "#mainEmailLogTable tbody>tr", function(item) {
    window.location="{{ action('EmailController@email_log_details') }}/"+item.currentTarget.id.split('_')[1];
  });
  
  
  var dt = $("#mainEmailLogTable").DataTable({
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@email_log') }}",
        type: "POST"
      },
      columnDefs: [{ targets: 'no-sort', orderable: false}]
    });
</script>
@endsection