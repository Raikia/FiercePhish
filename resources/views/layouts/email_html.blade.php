@if (stripos($data, '<html>') === false)
<html>
@endif
@if (stripos($data, '<body>') === false)
<body>
@endif
{!! $data !!}
@if (stripos($data, '</body>') === false)
</body>
@endif
@if (stripos($data, '</html>') === false)
</html>
@endif