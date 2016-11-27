@extends('layouts.app')

@section('content')
<div class="page-title">
  <div class="title_left">
    <h3>Send Single Email</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
  <div class="col-md-6 col-sm-6 col-xs-6">
    <div class="x_panel">
      <div class="x_content">
          <form class="form-horizontal form-label-left input_mask">

              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sender Name <span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" placeholder="Bill Smith">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Sender Email <span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="email" class="form-control" placeholder="bsmith@malicious.com">
                </div>
              </div>
              <div class="ln_solid"></div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Receiver Name <span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="text" class="form-control" placeholder="John Doe">
                </div>
              </div>
              <div class="form-group">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Receiver Email <span class="required">*</span></label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                  <input type="email" class="form-control" placeholder="john.doe@domain.com">
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
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Attachment</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <input type="file" name="attach" class="form-control">
                </div>
              </div>
              <div class="form-group date">
                <label class="control-label col-md-3 col-sm-3 col-xs-12">Send TLS?</label>
                <div class="col-md-9 col-sm-9 col-xs-12">
                    <div class="radio">
                        <label>
                            <input type="radio" name="sendTLS" checked /> Yes
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="sendTLS" /> No
                        </label>
                    </div>
                </div>
              </div>
            </form>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="x_panel">
    <form class="form-horizontal form-label-left input_mask">
      <textarea rows="10" id="bodyMsg" name="bodyMsg" class="bodyMsg"></textarea>
    </form>
    </div>
  </div>
</div>
@endsection



@section('footer')
<script type="text/javascript">
/* global $ */
    CKEDITOR.replace('bodyMsg');
</script>
@endsection