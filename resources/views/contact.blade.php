@extends('layouts.app')

@section('title', __('messages.contact_page_title'))

@section('content')
<style>
    .contact-hero {
        background: linear-gradient(120deg, #eaf6f7, #ffffff);
        padding: 85px 20px;
        text-align: center;
    }

    .contact-hero h1 {
        font-size: clamp(36px, 5vw, 54px);
        margin: 0 0 16px;
        color: #19343d;
    }

    .contact-hero h1 span {
        color: #087e8b;
    }

    .contact-hero p {
        max-width: 650px;
        margin: auto;
        color: #637780;
        font-size: 17px;
    }

    .contact-section {
        padding: 85px 0;
    }

    .contact-grid {
        display: grid;
        grid-template-columns: 0.85fr 1.15fr;
        gap: 35px;
        align-items: start;
    }

    .contact-info,
    .contact-form-card {
        background: #ffffff;
        border: 1px solid #e1edef;
        border-radius: 20px;
        padding: 35px;
        box-shadow: 0 10px 35px rgba(25, 52, 61, 0.05);
    }

    .contact-info h2,
    .contact-form-card h2 {
        font-size: 27px;
        margin: 0 0 12px;
    }

    .contact-intro {
        color: #637780;
        margin-bottom: 30px;
    }

    .contact-item {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        margin-bottom: 28px;
    }

    .contact-icon {
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        display: grid;
        place-items: center;
        border-radius: 13px;
        background: #e4f3f5;
        font-size: 23px;
    }

    .contact-item h3 {
        margin: 0 0 5px;
        font-size: 17px;
    }

    .contact-item p {
        margin: 0;
        color: #637780;
        font-size: 14px;
    }

    .contact-form {
        display: grid;
        gap: 20px;
    }

    .contact-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 700;
        font-size: 14px;
    }

    .contact-field input,
    .contact-field select,
    .contact-field textarea {
        width: 100%;
        padding: 14px 16px;
        border: 1px solid #d4e4e7;
        border-radius: 10px;
        font: inherit;
        background: #fbfdfd;
        color: #19343d;
    }

    .contact-field textarea {
        min-height: 145px;
        resize: vertical;
    }

    .contact-field input:focus,
    .contact-field select:focus,
    .contact-field textarea:focus {
        outline: 2px solid #087e8b;
        outline-offset: 2px;
    }

    .contact-submit {
        width: 100%;
        cursor: pointer;
    }

    .contact-notice {
        color: #637780;
        font-size: 13px;
        margin: 0;
    }

    .contact-demo-message {
        display: none;
        padding: 14px;
        border-radius: 10px;
        background: #eaf6f7;
        color: #087e8b;
        font-weight: 600;
    }

    .contact-demo-message.visible {
        display: block;
    }

    @media (max-width: 800px) {
        .contact-grid {
            grid-template-columns: 1fr;
        }

        .contact-section {
            padding: 55px 0;
        }

        .contact-info,
        .contact-form-card {
            padding: 24px;
        }

        .contact-hero {
            padding: 65px 20px;
        }
    }
</style>

<section class="contact-hero">
    <h1>
        {{ __('messages.contact_hero_title') }}
        <span>{{ __('messages.contact_hero_highlight') }}</span>
    </h1>

    <p>
        {{ __('messages.contact_hero_description') }}
    </p>
</section>

<section class="contact-section">
    <div class="container contact-grid">

        <!-- Contact Information -->
        <div class="contact-info">
            <h2>{{ __('messages.contact_info_title') }}</h2>

            <p class="contact-intro">
                {{ __('messages.contact_info_description') }}
            </p>

            <div class="contact-item">
                <div class="contact-icon" aria-hidden="true">📍</div>
                <div>
                    <h3>{{ __('messages.contact_location') }}</h3>
                    <p>{{ __('messages.contact_location_desc') }}</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon" aria-hidden="true">📧</div>
                <div>
                    <h3>{{ __('messages.contact_email') }}</h3>
                    <p>{{ __('messages.contact_email_desc') }}</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon" aria-hidden="true">📞</div>
                <div>
                    <h3>{{ __('messages.contact_phone') }}</h3>
                    <p>{{ __('messages.contact_phone_desc') }}</p>
                </div>
            </div>

            <div class="contact-item">
                <div class="contact-icon" aria-hidden="true">🕒</div>
                <div>
                    <h3>{{ __('messages.contact_hours') }}</h3>
                    <p>{{ __('messages.contact_hours_desc') }}</p>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="contact-form-card">
            <h2>{{ __('messages.contact_form_title') }}</h2>

            <p class="contact-intro">
                {{ __('messages.contact_form_description') }}
            </p>

            <form class="contact-form" id="contactForm">

                <div class="contact-field">
                    <label for="contactName">
                        {{ __('messages.contact_name_label') }}
                    </label>

                    <input
                        type="text"
                        id="contactName"
                        name="name"
                        placeholder="{{ __('messages.contact_name_placeholder') }}"
                        autocomplete="name"
                        required>
                </div>

                <div class="contact-field">
                    <label for="contactEmail">
                        {{ __('messages.contact_email_label') }}
                    </label>

                    <input
                        type="email"
                        id="contactEmail"
                        name="email"
                        placeholder="{{ __('messages.contact_email_placeholder') }}"
                        autocomplete="email"
                        required>
                </div>

                <div class="contact-field">
                    <label for="contactSubject">
                        {{ __('messages.contact_subject_label') }}
                    </label>

                    <select id="contactSubject" name="subject" required>
                        <option value="">
                            {{ __('messages.contact_subject_placeholder') }}
                        </option>

                        <option value="general">
                            {{ __('messages.contact_subject_general') }}
                        </option>

                        <option value="appointments">
                            {{ __('messages.contact_subject_appointments') }}
                        </option>

                        <option value="services">
                            {{ __('messages.contact_subject_services') }}
                        </option>

                        <option value="ai">
                            {{ __('messages.contact_subject_ai') }}
                        </option>

                        <option value="other">
                            {{ __('messages.contact_subject_other') }}
                        </option>
                    </select>
                </div>

                <div class="contact-field">
                    <label for="contactMessage">
                        {{ __('messages.contact_message_label') }}
                    </label>

                    <textarea
                        id="contactMessage"
                        name="message"
                        placeholder="{{ __('messages.contact_message_placeholder') }}"
                        maxlength="2000"
                        required></textarea>
                </div>

                <button type="submit" class="btn contact-submit">
                    {{ __('messages.contact_submit') }}
                </button>

                <p class="contact-notice">
                    {{ __('messages.contact_notice') }}
                </p>

                <div
                    id="contactDemoMessage"
                    class="contact-demo-message"
                    role="status"
                    aria-live="polite">
                    {{ __('messages.contact_success') }}
                </div>

            </form>
        </div>

    </div>
</section>

<script>
    document.getElementById('contactForm')
        .addEventListener('submit', function(event) {
            event.preventDefault();

            document.getElementById('contactDemoMessage')
                .classList.add('visible');
        });
</script>

@endsection