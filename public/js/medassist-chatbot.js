
/*
 * TAQWEEM Dental - Floating Chatbot
 * Lightweight Arabic / English assistant
 */

document.addEventListener("DOMContentLoaded", () => {
    const chatbot = document.getElementById("medassist-chatbot");

    if (!chatbot) return;

    const toggle = document.getElementById("medassist-chat-toggle");
    const windowBox = document.getElementById("medassist-chat-window");
    const closeButton = document.getElementById("medassist-chat-close");
    const messages = document.getElementById("medassist-chat-messages");
    const form = document.getElementById("medassist-chat-form");
    const input = document.getElementById("medassist-chat-input");
    const quickButtons = chatbot.querySelectorAll("[data-chat-question]");

    const isArabic = chatbot.getAttribute("lang") === "ar";

    // Temporary local responses until Laravel backend is connected.
    const responses = {
        booking: {
            ar: "لحجز موعد في تقويم، افتح صفحة الأطباء، اختار الطبيب المناسب، وبعدها تابع خطوات الحجز.",
            en: "To book an appointment, visit our dentists page, choose a dentist, and follow the booking steps."
        },
        dentists: {
            ar: "بتقدر تشوف أطباء الأسنان المتاحين وتختار الطبيب المناسب إلك من صفحة الأطباء.",
            en: "You can explore available dentists and choose the right dentist from our dentists directory."
        },
        smile: {
            ar: "ميزة محاكاة الابتسامة بتسمح إلك ترفع صورة ابتسامتك وتشوف تصور تجميلي باستخدام الذكاء الاصطناعي. النتائج تقريبية وليست ضماناً للعلاج.",
            en: "Smile Simulation lets you upload a smile photo and preview possible cosmetic changes using AI. Results are illustrative, not guaranteed treatment outcomes."
        },
        greeting: {
            ar: "أهلاً وسهلاً في تقويم! 🦷 كيف بقدر أساعدك؟",
            en: "Welcome to TAQWEEM! 🦷 How can I help you?"
        },
        thanks: {
            ar: "أهلاً وسهلاً فيك! 💚 إذا عندك أي سؤال ثاني أنا هون.",
            en: "You're welcome! 💚 I'm here if you have more questions."
        },
        fallback: {
            ar: "بقدر أساعدك بمعلومات عن حجز المواعيد، أطباء الأسنان ومحاكاة الابتسامة. شو بتحب تعرف؟",
            en: "I can help with appointments, dentists, and Smile Simulation. What would you like to know?"
        }
    };

    function addMessage(text, sender) {
        const message = document.createElement("div");

        message.className = "medassist-chat-message " + sender;
        message.textContent = text;

        messages.appendChild(message);
        messages.scrollTop = messages.scrollHeight;
    }

    function openChat() {
        windowBox.hidden = false;
        toggle.setAttribute("aria-expanded", "true");
        input.focus();
    }

    function closeChat() {
        windowBox.hidden = true;
        toggle.setAttribute("aria-expanded", "false");
        toggle.focus();
    }

    toggle.addEventListener("click", () => {
        if (windowBox.hidden) {
            openChat();
        } else {
            closeChat();
        }
    });

    closeButton.addEventListener("click", closeChat);

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && !windowBox.hidden) {
            closeChat();
        }
    });

    function detectIntent(message) {
        const text = message.toLowerCase().trim();

        if (
            /حجز|موعد|مواعيد|appointment|booking|book/.test(text)
        ) {
            return "booking";
        }

        if (
            /طبيب|دكتور|دكتورة|اطباء|أطباء|dentist|doctor/.test(text)
        ) {
            return "dentists";
        }

        if (
            /ابتسامة|تبييض|اسنان|أسنان|smile|whitening|veneer/.test(text)
        ) {
            return "smile";
        }

        if (
            /مرحبا|مرحباً|اهلا|أهلا|السلام|hello|hi|hey/.test(text)
        ) {
            return "greeting";
        }

        if (
            /شكرا|شكراً|يسلمو|thanks|thank you/.test(text)
        ) {
            return "thanks";
        }

        return "fallback";
    }

    function replyTo(message, intent = null) {
        const detectedIntent = intent || detectIntent(message);
        const language = isArabic ? "ar" : "en";

        addMessage(message, "user");

        const response = responses[detectedIntent] || responses.fallback;

        // Short delay for a natural conversation feel.
        setTimeout(() => {
            addMessage(response[language], "bot");
        }, 250);
    }

    quickButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const intent = button.dataset.chatQuestion;
            replyTo(button.textContent.trim(), intent);
        });
    });

    form.addEventListener("submit", (event) => {
        event.preventDefault();

        const message = input.value.trim();

        if (!message) return;

        input.value = "";
        replyTo(message);
    });
});
