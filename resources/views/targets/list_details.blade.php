@extends('layouts.app', ['title' => 'Target List Details'])

@section('content')
<div class="page-title">
    <div class="title_left">
        <h3>Details on &quot;{{ $targetList->name }}&quot; Target List</h3>
    </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-5 col-sm-5 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Target List Overview</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table class="table table-striped table-bordered" style="width: 100%; margin-left: auto; margin-right: auto;">
                    <tbody>
                        <tr>
                            <td style="width: 27%;"><b>List Name</b></td>
                            <td>{{ $targetList->name }}</td>
                        </tr>
                        <tr>
                            <td><b>Created at</b></td>
                            <td>{{ \App\Libraries\DateHelper::readable($targetList->created_at) }}</td>
                        </tr>
                        <tr>
                            <td><b>Updated at</b></td>
                            <td>{{ \App\Libraries\DateHelper::readable($targetList->updated_at) }}</td>
                        </tr>
                        <tr>
                            <td><b># of users</b></td>
                            <td>{{ $targetList->users()->count() }}</td>
                        </tr>
                        <tr>
                            <td><b>Notes</b></td>
                            <td><a href="#" class="editnotes" data-type="text" data-pk="{{ $targetList->id }}" data-url="{{ action('AjaxController@edit_targetlist_notes') }}" data-title="Enter note">{{ $targetList->notes }}</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="x_panel">
            <div class="x_title">
                <h2>Individual Actions</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <a href="{{ action('TargetsController@assign_index', ['id' => $targetList->id]) }}" class="btn btn-primary">Add individual users to List</a><br /><br />
            </div>
            <div class="x_title">
                <h2>Bulk Actions</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="{{ action('TargetsController@addAlltoList', ['id' => $targetList->id]) }}" id="addAlltoList" method="post">
                    {{ csrf_field() }}
                    <button class="btn btn-success">Add all Target Users to List</button>
                    Only unassigned targets: <input type="checkbox" name="unusedOnly" value="unassigned_only" style="margin-right: 10px;" /><br /><br />
                </form>
                <form action="{{ action('TargetsController@addRandomtoList', ['id' => $targetList->id]) }}" method="post">
                    {{ csrf_field() }}
                    <input type="number" placeholder="X amount" min="1" max="{{ $numTargetUsers }}" name="numToSelect" style="padding-left: 5px; width: 80px; height: 35px;" />
                    <input type="submit" class="btn btn-success" id="numToSelect_btn" value="Select X Amount Randomly" style="margin-left: 10px; margin-right: 10px;" />
                    Only unassigned targets: <input type="checkbox" name="unusedOnly" value="unassigned_only" style="margin-right: 10px;" /><br /><br />
                </form>
            </div>
            <div class="x_title">
                <h2>Removal Actions</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form action="{{ action('TargetsController@clearList', ['id' => $targetList->id]) }}" id="removeAllUsersForm" method="post">
                    {{ csrf_field() }}
                    <button class="btn btn-danger">Remove all users from List</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7 col-sm-7 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Target Users on List</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <table id="targetuserslist" class="table table-striped table-bordered datatable">
                    <thead>
                        <tr>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Notes</th>
                            <th style="width: 1px;"></th>
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
    /* global bootbox */
    /*
    var dt = $(".datatable").DataTable({
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_membership', ['id' => $targetList->id]) }}",
        type: "POST"
      },
      columnDefs: [{ targets: 'no-sort', orderable: false}]
    });
    */
    
    var dt = $(".datatable").DataTable({
      language: {
        "emptyTable": "No Target Users Found"
      },
      serverSide: true,
      processing: true,
      ajax: {
        url: "{{ action('AjaxController@targetuser_membership', ['id' => $targetList->id]) }}",
        type: "POST"
      },
      columns: [
        { data: 'first_name', name: 'first_name'},
        { data: 'last_name', name: 'last_name'},
        { data: 'email', name: 'email'},
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
        },
        { data: 'delete', name: 'delete', orderable: false, searchable: false, sortable: false},
      ],
      columnDefs: [ {
          "targets": 4,
          "data": null,
          "defaultContent": '<a class="removeUser single_pointer"><i style="color: #D9534F;" class="fa fa-times"></i></a>'
      }]
    });
    
    $("#removeAllUsersForm").submit(function(e) {
        var currentForm = this;
        e.preventDefault();
        bootbox.confirm("Are you sure you want to remove all the users from the list?", function(result) {
            if (result) {
                currentForm.submit();
            }
        });
    });
    
    $("#addAlltoList").submit(function(e) {
        var currentForm = this;
        e.preventDefault();
        bootbox.confirm("Are you sure you want to add all users to the list?", function(result) {
            if (result) {
                currentForm.submit();
            }
        });
    });
    
    $(".editnotes").editable();
    
    $(".datatable").editable({
      selector: 'tr td:nth-child(4) a',
      emptytext: 'Empty'
    });
    
    $(".editnotes").on('save', function() {
        setTimeout(function() {
            dt.rows().invalidate();
        }, 500);
    });
    
    $(document.body).on('click', '.removeUser', function() {
        var id = $(this).parent().parent().attr('id').split('_')[1];
        $('<form action="{{ action('TargetsController@removeUser', ['id' => $targetList->id]) }}/'+id+'" method="post"><input type="hidden" name="_token" value="{{ csrf_token() }}" /></form>').appendTo('body').submit();
    });
    
    CURRENT_URL = "{{ action('TargetsController@targetlists_index') }}";
</script>
@endsection