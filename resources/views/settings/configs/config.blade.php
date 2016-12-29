@extends('layouts.app', ['title' => 'Configuration'])

@section('content')

<div class="page-title">
  <div class="title_left">
    <h3>Application Configuration Settings</h3>
  </div>
</div>

<div class="clearfix"></div>

<div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>General Application Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@post_config') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Application URL
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="APP_URL" class="form-control col-md-7 col-xs-12" value="{{ env('APP_URL') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Application Debugging
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select name="APP_DEBUG" class="form-control col-md-7 col-xs-12">
                                <option>true</option>
                                <option{{ (env('APP_DEBUG')?'':' selected') }}>false</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Testing mode (no email sending)
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select name="TEST_EMAIL_JOB" class="form-control col-md-7 col-xs-12">
                                <option>true</option>
                                <option{{ (env('TEST_EMAIL_JOB')?'':' selected') }}>false</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Prefix of FirePhish
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="URI_PREFIX" class="form-control col-md-7 col-xs-12" value="{{ env('URI_PREFIX') }}">
                        </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">Save Settings</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
        <div class="x_panel">
            <div class="x_title">
                <h2>Email Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@post_config') }}">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">BCC All Emails
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="MAIL_BCC_ALL" class="form-control col-md-7 col-xs-12" value="{{ env('MAIL_BCC_ALL') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Mail Configuration
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <select name="MAIL_DRIVER" id="MAIL_DRIVER" class="form-control col-md-7 col-xs-12">
                                <option value="smtp"{{ (env('MAIL_DRIVER')=='smtp'?' selected':'') }}>SMTP</option>
                                <option value="mailgun"{{ (env('MAIL_DRIVER')=='mailgun'?' selected':'') }}>Mailgun</option>
                            </select>
                        </div>
                    </div>
                    <div class="conditional_settings" id="smtp_settings" style="display: none;">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">SMTP Host
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="MAIL_HOST" class="form-control col-md-7 col-xs-12" value="{{ env('MAIL_HOST') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">SMTP Port
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="MAIL_PORT" class="form-control col-md-7 col-xs-12" value="{{ env('MAIL_PORT') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">SMTP Username
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="MAIL_USERNAME" class="form-control col-md-7 col-xs-12" value="{{ env('MAIL_USERNAME') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">SMTP Password
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="password" name="MAIL_PASSWORD" class="form-control col-md-7 col-xs-12" value="{{ env('MAIL_PASSWORD') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">SMTP Encryption Type
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <select name="MAIL_ENCRYPTION" class="form-control col-md-7 col-xs-12">
                                    <option value=""{{ (env('MAIL_ENCRYPTION')==''?' selected':'') }}>None</option>
                                    <option value="tls"{{ (env('MAIL_ENCRYPTION')=='tls'?' selected':'') }}>TLS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="conditional_settings" id="mailgun_settings" style="display: none;">
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Mailgun Domain
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="MAILGUN_DOMAIN" class="form-control col-md-7 col-xs-12" value="{{ env('MAILGUN_DOMAIN') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-3 col-sm-3 col-xs-12">Mailgun Secret API
                            </label>
                            <div class="col-md-6 col-sm-6 col-xs-12">
                                <input type="text" name="MAILGUN_SECRET" class="form-control col-md-7 col-xs-12" value="{{ env('MAILGUN_SECRET') }}">
                            </div>
                        </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">Save Settings</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-sm-6 col-xs-12">
        <div class="x_panel">
            <div class="x_title">
                <h2>Database Settings</h2>
                <div class="clearfix"></div>
            </div>
            <div class="x_content">
                <form class="form-horizontal form-label-left" method="post" action="{{ action('SettingsController@post_config') }}">
                    <div style="text-align: center; color: #FF0000;" class="form-group">
                        Careful when editing these settings. If you mess up, you'll have to manually edit "{{ base_path('.env') }}"
                    </div>
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Type
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="DB_CONNECTION" class="form-control col-md-7 col-xs-12" value="{{ env('DB_CONNECTION') }}" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Host
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="DB_HOST" class="form-control col-md-7 col-xs-12" value="{{ env('DB_HOST') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Port
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="DB_PORT" class="form-control col-md-7 col-xs-12" value="{{ env('DB_PORT') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Name
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="DB_DATABASE" class="form-control col-md-7 col-xs-12" value="{{ env('DB_DATABASE') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Username
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="text" name="DB_USERNAME" class="form-control col-md-7 col-xs-12" value="{{ env('DB_USERNAME') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-md-3 col-sm-3 col-xs-12">Database Password
                        </label>
                        <div class="col-md-6 col-sm-6 col-xs-12">
                            <input type="password" name="DB_PASSWORD" class="form-control col-md-7 col-xs-12" value="{{ env('DB_PASSWORD') }}">
                        </div>
                    </div>
                    <div class="ln_solid"></div>
                    <div class="form-group">
                        <div class="col-md-6 col-sm-6 col-xs-12 col-md-offset-3">
                            <button type="submit" class="btn btn-success">Save Settings</button>
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
    function select_mail_settings()
    {
        $(".conditional_settings").css('display', 'none');
        $("#"+$("#MAIL_DRIVER").val()+"_settings").css('display', 'block');
    }

    $("#MAIL_DRIVER").change(select_mail_settings);
    
    $(document).ready(function() {
        select_mail_settings();
    });
</script>
@endsection