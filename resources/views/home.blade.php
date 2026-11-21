@extends('layouts.app')

@section('title', 'MedAssist | ' . __('messages.hero_title'))

@section('content')

<section class="hero" id="home">
    <div class="container hero-grid">
        <div>
            <span class="eyebrow">{{ __('messages.hero_eyebrow') }}</span>
            <h1>
                {{ __('messages.hero_title') }}
                <span>{{ __('messages.hero_title_highlight') }}</span>
            </h1>
            <p>{{ __('messages.hero_description') }}</p>

            <div class="hero-actions">
                <a class="btn" href="{{ route('smile.create') }}">
                    {{ __('messages.hero_try_ai') }}
                </a>
                <a class="btn btn-outline" href="#services">
                    {{ __('messages.hero_explore') }}
                </a>
            </div>
        </div>

        <div class="hero-visual">
            <img
                src="https://images.unsplash.com/photo-1606811971618-4486d14f3f99?auto=format&amp;fit=crop&amp;w=1000&amp;q=85"
                alt="{{ __('messages.hero_image_alt') }}"
                loading="eager">
            <div class="hero-note">
                🦷 {{ __('messages.hero_note') }}
            </div>
        </div>
    </div>
</section>

<section class="section section-light" id="services">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">{{ __('messages.services_eyebrow') }}</span>
            <h2>{{ __('messages.services_title') }}</h2>
            <p>{{ __('messages.services_description') }}</p>
        </div>

        <div class="grid grid-3">
            <article class="tile">
                <div class="symbol">🦷</div>
                <h3>{{ __('messages.service_general') }}</h3>
                <p>{{ __('messages.service_general_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">✨</div>
                <h3>{{ __('messages.service_cosmetic') }}</h3>
                <p>{{ __('messages.service_cosmetic_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">😁</div>
                <h3>{{ __('messages.service_orthodontics') }}</h3>
                <p>{{ __('messages.service_orthodontics_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">🔬</div>
                <h3>{{ __('messages.service_implants') }}</h3>
                <p>{{ __('messages.service_implants_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">💙</div>
                <h3>{{ __('messages.service_root') }}</h3>
                <p>{{ __('messages.service_root_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">🤖</div>
                <h3>{{ __('messages.smile_simulation') }}</h3>
                <p>{{ __('messages.service_ai_desc') }}</p>
            </article>
        </div>
    </div>
</section>

<section class="section" id="doctors">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">{{ __('messages.doctors_eyebrow') }}</span>
            <h2>{{ __('messages.doctors_title') }}</h2>
            <p>{{ __('messages.doctors_description') }}</p>
        </div>

        <div class="grid grid-3">
            <article class="tile doctor-card">
                <div class="doctor-image" aria-hidden="true">👨‍⚕️</div>
                <div class="doctor-info">
                    <h3>{{ __('messages.doctor_ahmad') }}</h3>
                    <strong>{{ __('messages.doctor_ahmad_specialty') }}</strong>
                    <p>{{ __('messages.doctor_ahmad_desc') }}</p>
                </div>
            </article>

            <article class="tile doctor-card">
                <div class="doctor-image" aria-hidden="true">👩‍⚕️</div>
                <div class="doctor-info">
                    <h3>{{ __('messages.doctor_sara') }}</h3>
                    <strong>{{ __('messages.doctor_sara_specialty') }}</strong>
                    <p>{{ __('messages.doctor_sara_desc') }}</p>
                </div>
            </article>

            <article class="tile doctor-card">
                <div class="doctor-image" aria-hidden="true">👨‍⚕️</div>
                <div class="doctor-info">
                    <h3>{{ __('messages.doctor_omar') }}</h3>
                    <strong>{{ __('messages.doctor_omar_specialty') }}</strong>
                    <p>{{ __('messages.doctor_omar_desc') }}</p>
                </div>
            </article>
        </div>

        <p class="disclaimer">{{ __('messages.doctors_disclaimer') }}</p>
    </div>
</section>

<section class="section section-light" id="why-choose">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">{{ __('messages.why_eyebrow') }}</span>
            <h2>{{ __('messages.why_title') }}</h2>
            <p>{{ __('messages.why_description') }}</p>
        </div>

        <div class="grid grid-4">
            <article class="tile">
                <div class="symbol">🤖</div>
                <h3>{{ __('messages.why_previews') }}</h3>
                <p>{{ __('messages.why_previews_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">🦷</div>
                <h3>{{ __('messages.why_specialists') }}</h3>
                <p>{{ __('messages.why_specialists_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">📅</div>
                <h3>{{ __('messages.why_booking') }}</h3>
                <p>{{ __('messages.why_booking_desc') }}</p>
            </article>

            <article class="tile">
                <div class="symbol">💙</div>
                <h3>{{ __('messages.why_patient') }}</h3>
                <p>{{ __('messages.why_patient_desc') }}</p>
            </article>
        </div>
    </div>
</section>

<section class="section ai-section" id="ai-smile">
    <div class="container ai-grid">
        <div>
            <span class="eyebrow">{{ __('messages.ai_eyebrow') }}</span>
            <h2>{{ __('messages.ai_title') }}</h2>
            <p>{{ __('messages.ai_description') }}</p>
            <p>
                <strong>{{ __('messages.ai_note_label') }}</strong>
                {{ __('messages.ai_note') }}
            </p>
            <a class="btn" href="{{ route('smile.create') }}">
                {{ __('messages.ai_button') }}
            </a>
        </div>

        <div class="ai-panel">
            <div class="steps">
                <div class="step">
                    <span class="step-number">1</span>
                    <span>{{ __('messages.ai_step_1') }}</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span>{{ __('messages.ai_step_2') }}</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span>{{ __('messages.ai_step_3') }}</span>
                </div>
                <div class="step">
                    <span class="step-number">4</span>
                    <span>{{ __('messages.ai_step_4') }}</span>
                </div>
            </div>
            <p class="disclaimer">{{ __('messages.ai_disclaimer') }}</p>
        </div>
    </div>
</section>

<section class="section" id="faq">
    <div class="container">
        <div class="section-head">
            <span class="eyebrow">{{ __('messages.faq_eyebrow') }}</span>
            <h2>{{ __('messages.faq_title') }}</h2>
            <p>{{ __('messages.faq_description') }}</p>
        </div>

        <div class="faq-list">
            <details>
                <summary>{{ __('messages.faq_q1') }}</summary>
                <p>{{ __('messages.faq_a1') }}</p>
            </details>

            <details>
                <summary>{{ __('messages.faq_q2') }}</summary>
                <p>{{ __('messages.faq_a2') }}</p>
            </details>

            <details>
                <summary>{{ __('messages.faq_q3') }}</summary>
                <p>{{ __('messages.faq_a3') }}</p>
            </details>

            <details>
                <summary>{{ __('messages.faq_q4') }}</summary>
                <p>{{ __('messages.faq_a4') }}</p>
            </details>
        </div>
    </div>
</section>

<section class="cta" id="get-started">
    <div class="container">
        <h2>{{ __('messages.cta_title') }}</h2>
        <p>{{ __('messages.cta_description') }}</p>
        <a class="btn" href="{{ route('smile.create') }}">
            {{ __('messages.ai_button') }}
        </a>
    </div>
</section>

@endsection