@php
try {
        echo Html2Text\Html2Text::convert(preg_replace('/(<[^>]+) id=".*?"/i', '$1', $data));
} catch (\Exception $e) {
        echo strip_tags($data);
}
@endphp