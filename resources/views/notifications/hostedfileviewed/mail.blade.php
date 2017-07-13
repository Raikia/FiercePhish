@component('mail::message')
# Hosted File View Notification for "{{ $visit->hostfile->file_name }}"

@if ($invalid)
## _**!!! This was an invalid tracker view !!!**_
@endif

### File Information:

* **Original filename:** {{ $visit->hostfile->original_file_name }}
* **Hosted filename:**  {{ $visit->hostfile->getPath() }}
@if ($visit->hostfile->site !== null)
* **Spoofed Site:** {{ $visit->hostfile->site->name }}
@endif

@if ($visit->email !== null)
@component('mail::panel')
### User information:

* **User:** {{ $visit->email->targetuser->full_name() }}
* **Email:** {{ $visit->email->targetuser->email }}
@if ($visit->email->campaign !== null)
* **Campaign:** {{ $visit->email->campaign->name }}
@endif
@if ($visit->email->targetuser->notes !== null)
* **User Note:** {{ $visit->email->targetuser->notes }}
@endif
@if ($visit->email->campaign->target_list->notes !== null)
* **List Note:** {{ $visit->email->campaign->target_list->notes }}
@endif
@endcomponent
@endif

### View Metadata

* **IP Address:**  {{ $visit->ip }}  ([whois](https://whois.domaintools.com/{{ $visit->ip }}), [geolocate](http://ipinfo.io/{{ $visit->ip }}))
* **Browser:**  {{ $visit->browser }} v{{ $visit->browser_version }} (by {{ $visit->browser_maker }})
* **Platform:**  {{ $visit->platform }}
* **Raw Useragent:**  {{ $visit->useragent }}
@if ($visit->referer !== null)
* **Referer:**  {{ \App\Libraries\GlobalHelper::makeUnclickableLink($visit->referer) }}
@endif

@if ($visit->credentials !== null)
### Credentials!
* Username: {{ $visit->credentials->username }}
@if ($visit->hostfile->site !== null)
* **Password:** [click here]({{ action('HostedSiteController@site_file_details', ['id' => $visit->hostfile->id]) }})
@else
* **Password:** [click here]({{ action('HostedFileController@file_details', ['id' => $visit->hostfile->id]) }})
@endif
@endif

_To disable these notifications, [click here]({{ action('SettingsController@get_editprofile') }})_
@endcomponent