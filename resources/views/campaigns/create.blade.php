@extends('layouts.app')

@section('content')
<div class="page-title">
  <div class="title_left">
    <h3>Create New Campaign</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <form class="form-horizontal form-label-left input_mask">

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Campaign Name</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" placeholder="Default Input">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Campaign Description</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <textarea rows="5" class="form-control" style="width: 100%"></textarea>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Email Template</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <select id="templateSelect" class="form-control select2_single">
                        <option></option>
                        <option>test template</option>
                    </select>
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Target List <span class="required">*</span>
                </label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <select id="listSelect" class="form-control select2_single">
                        <option></option>
                        <option>test list</option>
                    </select>
                </div>
              </div>
              <!--<div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-primary">Cancel</button>
                  <button type="submit" class="btn btn-success">Submit</button>
                </div>
              </div>
                -->
            </form>
      </div>
    </div>
  </div>
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <form class="form-horizontal form-label-left input_mask">

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sending Schedule</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="radio">
                        <label>
                            <input type="radio" name="scheduleSelect" /> Now
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <div class="form-group">
                                <input type="radio" style="margin-top: 20px;" name="scheduleSelect" />
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <input type="text" name="asdf" class="form-control" placeholder="Send #" /> 
                            </div>
                            <div class="col-md-6 col-sm-6 col-xs-12 form-group">
                                <input type="text" name="fdsa" class="form-control" placeholder="Every # minutes" />
                            </div>
                        </label>
                    </div>
                </div>
              </div>
              <div class="form-group date">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Starting date</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="starting_date" placeholder="Today" value="{{ date('m/d/Y') }}" />
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Starting time</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" id="starting_time" placeholder="Now" />
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Target List <span class="required">*</span>
                </label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <select id="listSelect" class="form-control select2_single">
                        <option></option>
                        <option>test list</option>
                    </select>
                </div>
              </div>
              <div class="ln_solid"></div>
              <div class="form-group">
                <div class="col-md-9 col-sm-9 col-xs-12 col-md-offset-3">
                  <button type="submit" class="btn btn-primary">Cancel</button>
                  <button type="submit" class="btn btn-success">Submit</button>
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
  $("#templateSelect").select2({
    placeholder: "Select an Email Template",
    allowClear: true
  });
  $("#listSelect").select2({
    placeholder: "Select an Target List",
    allowClear: true
  });
  
  $("#starting_date").datepicker({
    startDate: "{{ date('m/d/Y') }}",
    todayHighlight: true,
    autoclose: true,
    todayBtn: "linked",
  });
  
  $("#starting_time").timepicker({
    scrollDefault: "now",
    step: 5,
    
  });
</script>
@endsection