<!DOCTYPE html>
<html>

<head>
    <title>Access Granted</title>
</head>

<body>
    <p>You have been granted access to view the content. Click the link below:</p>
    <a href="{{ route('accestokens.activateAccessToken', ['token' => $token]) }}">Access Content</a>
</body>

</html>
