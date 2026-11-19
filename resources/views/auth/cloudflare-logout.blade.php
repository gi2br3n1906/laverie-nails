<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="1;url={{ $homeUrl }}">
    <title>{{ __('ui.profile.title') }} | Laverie Nails</title>
</head>
<body>
    <p>Signing out. <a href="{{ $homeUrl }}">Return to the storefront</a>.</p>
    <script>
        (() => {
            const cleanup = [
                fetch('/cdn-cgi/access/logout', { credentials: 'include' }),
                @if (filled($teamDomain))
                    fetch(@json($teamDomain.'/cdn-cgi/access/logout'), {
                        mode: 'no-cors',
                        credentials: 'include',
                    }),
                @endif
            ];

            Promise.race([
                Promise.allSettled(cleanup),
                new Promise((resolve) => window.setTimeout(resolve, 400)),
            ]).finally(() => window.location.replace(@json($homeUrl)));
        })();
    </script>
</body>
</html>