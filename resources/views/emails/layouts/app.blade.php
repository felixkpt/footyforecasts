<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @vite('resources/ts/app.tsx')
    <style>
        p {
            font-size: 12px;
        }

        .signature {
            font-style: italic;
        }
    </style>
</head>

<body>
    <section class="max-w-2xl px-6 py-8 mx-auto bg-white dark:bg-gray-900">
        <header>
            <a href="#">
                <img class="w-10 h-7 sm:h-8" src="{{ asset('logo.png') }}" alt="App logo">
            </a>
        </header>
        <main class="mt-8">
            @yield('content')
        </main>
        <footer class="mt-8">
            <p class="text-gray-500 dark:text-gray-400">
                This email was sent to <a href="#" class="text-blue-600 hover:underline dark:text-blue-400"
                    target="_blank">felixkpt@gmail.com</a>.
                If you'd rather not receive this kind of email, you can <a href="#"
                    class="text-blue-600 hover:underline dark:text-blue-400">unsubscribe</a> or <a href="#"
                    class="text-blue-600 hover:underline dark:text-blue-400">manage your email preferences</a>.
            </p>

            <p class="mt-3 text-gray-500 dark:text-gray-400">© {{ date('Y').' '. config('app.name') }}. All Rights
                Reserved.</p>
        </footer>
    </section>
</body>
</html>
