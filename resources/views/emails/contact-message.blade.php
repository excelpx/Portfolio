<!DOCTYPE html>
<html lang="en">
<body>
    <p><strong>Nama:</strong> {{ $name }}</p>
    <p><strong>Email:</strong> {{ $email }}</p>
    <p><strong>Subject:</strong> {{ $subject }}</p>
    <p><strong>Message:</strong></p>
    <p>{!! nl2br(e($message)) !!}</p>
</body>
</html>
