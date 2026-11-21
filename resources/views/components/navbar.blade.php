
<header class="navbar">
    <nav class="container nav-inner"
         aria-label="{{ app()->getLocale() === 'ar' ? 'القائمة الرئيسية' : 'Main navigation' }}">

        <a class="logo" href="{{ route('home') }}">
            MedAssist
        </a>

        <button class="menu-toggle"
                type="button"
                aria-label="{{ app()->getLocale() === 'ar' ? 'فتح أو إغلاق القائمة' : 'Toggle navigation' }}"
                aria-controls="navLinks"
                aria-expanded="false">
            ☰ {{ app()->getLocale() === 'ar' ? 'القائمة' : 'Menu' }}
        </button>

        <div class="nav-links" id="navLinks">

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
                {{ app()->getLocale() === 'ar' ? 'لماذا نحن' : 'Why Us' }}
            </a>

            <a href="{{ route('home') }}#faq">
                {{ app()->getLocale() === 'ar' ? 'الأسئلة الشائعة' : 'FAQ' }}
            </a>

            <a href="{{ route('smile.create') }}">
                {{ __('messages.smile_simulation') }}
            </a>

            <!-- Language Switcher -->
            <div class="language-switcher"
                 aria-label="{{ app()->getLocale() === 'ar' ? 'اختيار اللغة' : 'Choose language' }}">

                <a href="{{ route('language.switch', 'en') }}"
                   lang="en"
                   class="{{ app()->getLocale() === 'en' ? 'active' : '' }}"
                   @if(app()->getLocale() === 'en') aria-current="true" @endif>
                    EN
                </a>

                <span aria-hidden="true">|</span>

                <a href="{{ route('language.switch', 'ar') }}"
                   lang="ar"
                   class="{{ app()->getLocale() === 'ar' ? 'active' : '' }}"
                   @if(app()->getLocale() === 'ar') aria-current="true" @endif>
                    AR
                </a>

            </div>

        </div>
    </nav>
</header>
