<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Redirecting to VictoriaBank...</title>
</head>
<body>
<p>Redirecting to the payment page...</p>
<form id="vbForm" method="POST" action="{{ $endpoint }}">
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
</form>
<script>document.getElementById('vbForm').submit();</script>
</body>
</html>
