<footer class="footer">
    <div class="container">

        <div class="footer-grid">

            <div>
                <a class="logo" href="{{ route('home') }}">
                    MedAssist
                </a>

                <p>
                    {{ __('messages.footer_description') }}
                </p>
            </div>

            <div>
                <h3>{{ __('messages.footer_quick_links') }}</h3>

                <a href="{{ route('home') }}">
                    {{ __('messages.home') }}
                </a>

                <a href="{{ route('home') }}#services">
                    {{ __('messages.services') }}
                </a>

                <a href="{{ route('home') }}#doctors">
                    {{ __('messages.doctors') }}
                </a>

                <a href="{{ route('home') }}#why-choose">
                    {{ __('messages.footer_why_us') }}
                </a>
            </div>

            <div>
                <h3>{{ __('messages.footer_explore') }}</h3>

                <a href="{{ route('smile.create') }}">
                    {{ __('messages.footer_smile') }}
                </a>

                <a href="{{ route('home') }}#faq">
                    {{ __('messages.footer_faq') }}
                </a>

                <a href="{{ route('home') }}#get-started">
                    {{ __('messages.footer_get_started') }}
                </a>
            </div>

        </div>

        <div class="footer-bottom">
            &copy; {{ date('Y') }} MedAssist.
            {{ __('messages.footer_copyright') }}
            {{ __('messages.footer_disclaimer') }}
        </div>

    </div>
</footer>