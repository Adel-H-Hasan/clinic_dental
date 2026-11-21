
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="description"
          content="@yield('meta_description', 'MedAssist dental care, specialists, appointments and AI smile simulation.')">

    <title>
        @yield('title', 'MedAssist | Your Smile, Our Priority')
    </title>

    <link rel="stylesheet" href="{{ asset('css/medassist.css') }}">

    @stack('styles')
</head>

<body>

    <a class="skip-link" href="#main">
        {{ app()->getLocale() === 'ar' ? 'الانتقال إلى المحتوى' : 'Skip to content' }}
    </a>

    @include('components.navbar')

    <main id="main">
        @yield('content')
    </main>

    @include('components.footer')

    <script>
        const menuButton = document.querySelector('.menu-toggle');
        const navLinks = document.getElementById('navLinks');

        if (menuButton && navLinks) {
            menuButton.addEventListener('click', () => {
                const expanded =
                    menuButton.getAttribute('aria-expanded') === 'true';

                menuButton.setAttribute(
                    'aria-expanded',
                    String(!expanded)
                );

                navLinks.classList.toggle('open', !expanded);
            });

            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('open');
                    menuButton.setAttribute('aria-expanded', 'false');
                });
            });
        }
    </script>

    @stack('scripts')

</body>
</html>
