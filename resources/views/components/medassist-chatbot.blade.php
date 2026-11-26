
@php
    $chatLocale = app()->getLocale() === 'ar' ? 'ar' : 'en';
    $chatArabic = $chatLocale === 'ar';
@endphp

<div id="medassist-chatbot" class="medassist-chatbot"
     lang="{{ $chatLocale }}"
     dir="{{ $chatArabic ? 'rtl' : 'ltr' }}">

    <!-- Floating Chat Button -->
    <button type="button"
            class="medassist-chat-toggle"
            id="medassist-chat-toggle"
            aria-label="{{ $chatArabic ? 'افتح مساعد تقويم' : 'Open TAQWEEM Assistant' }}"
            aria-expanded="false"
            aria-controls="medassist-chat-window">
        <span aria-hidden="true">💬</span>
    </button>

    <!-- Chat Popup -->
    <section class="medassist-chat-window"
             id="medassist-chat-window"
             aria-label="{{ $chatArabic ? 'محادثة مساعد تقويم' : 'TAQWEEM Chat' }}"
             hidden>

        <!-- Header -->
        <header class="medassist-chat-header">
            <div class="medassist-chat-brand">
                <span class="medassist-chat-avatar">🦷</span>

                <div>
                    <strong>
                        {{ $chatArabic ? 'مساعد تقويم' : 'TAQWEEM Assistant' }}
                    </strong>

                    <small>
                        {{ $chatArabic
                            ? 'مساعدك لخدمات طب الأسنان'
                            : 'Your dental care assistant' }}
                    </small>
                </div>
            </div>

            <button type="button"
                    class="medassist-chat-close"
                    id="medassist-chat-close"
                    aria-label="{{ $chatArabic ? 'إغلاق المحادثة' : 'Close chat' }}">
                ✕
            </button>
        </header>

        <!-- Chat Messages -->
        <div class="medassist-chat-messages"
             id="medassist-chat-messages"
             role="log"
             aria-live="polite">

            <div class="medassist-chat-message bot">
                {{ $chatArabic
                    ? 'أهلاً وسهلاً فيك في تقويم! 🦷👋 أنا مساعدك الافتراضي. كيف بقدر أساعدك اليوم؟'
                    : 'Welcome to TAQWEEM! 🦷👋 I am your virtual dental assistant. How can I help you today?' }}
            </div>

        </div>

        <!-- Quick Questions -->
        <div class="medassist-chat-quick-actions">

            <button type="button" data-chat-question="booking">
                {{ $chatArabic ? '📅 حجز موعد' : '📅 Book Appointment' }}
            </button>

            <button type="button" data-chat-question="dentists">
                {{ $chatArabic ? '👨‍⚕️ أطباء الأسنان' : '👨‍⚕️ Dentists' }}
            </button>

            <button type="button" data-chat-question="smile">
                {{ $chatArabic ? '✨ محاكاة الابتسامة' : '✨ Smile Simulation' }}
            </button>

        </div>

        <!-- Message Input -->
        <form id="medassist-chat-form" class="medassist-chat-form">

            <input type="text"
                   id="medassist-chat-input"
                   maxlength="500"
                   autocomplete="off"
                   placeholder="{{ $chatArabic ? 'اكتب سؤالك هنا...' : 'Type your question here...' }}"
                   aria-label="{{ $chatArabic ? 'رسالتك' : 'Your message' }}"
                   required>

            <button type="submit"
                    aria-label="{{ $chatArabic ? 'إرسال الرسالة' : 'Send message' }}">
                ➤
            </button>

        </form>

    </section>
</div>
