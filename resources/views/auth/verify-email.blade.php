<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Verify your email</title>
    </head>
    <body>
        <main>
            <h1>Verify your email</h1>
            <p>You must verify your email address before continuing. Check your inbox and follow the verification link.</p>

            @if (session('status') === 'verification-link-sent')
                <p role="status">A new verification link has been sent to your email address.</p>
            @endif

            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit">Resend verification email</button>
            </form>
        </main>
    </body>
</html>
