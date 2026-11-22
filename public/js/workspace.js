document.querySelector('[data-menu]')?.addEventListener('click', event => {
    const button = event.currentTarget;
    const open = button.getAttribute('aria-expanded') !== 'true';
    button.setAttribute('aria-expanded', String(open));
    document.getElementById('workspace-sidebar').classList.toggle('is-open', open);
});
document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    document.getElementById('workspace-sidebar')?.classList.remove('is-open');
    document.querySelector('[data-menu]')?.setAttribute('aria-expanded','false');
});
document.querySelector('[data-errors]')?.focus();
document.querySelectorAll('form[method="post" i]').forEach(form => form.addEventListener('submit', event => {
    if (event.defaultPrevented || !form.checkValidity()) return;
    if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
    form.dataset.submitting = 'true';
    form.setAttribute('aria-busy', 'true');
    const button = event.submitter;
    if (button && !button.name) { button.dataset.originalText = button.textContent; button.textContent = window.TaqweemUI?.submitting || button.textContent; }
}));
window.addEventListener('pageshow', () => document.querySelectorAll('form[data-submitting]').forEach(form => {
    delete form.dataset.submitting; form.removeAttribute('aria-busy');
    form.querySelectorAll('[data-original-text]').forEach(button => {button.textContent=button.dataset.originalText;delete button.dataset.originalText;});
}));
// Preserve non-sensitive form drafts only for an explicit language change.
// Passwords, file uploads, clinical notes, and free-text reasons are never stored.
const draftKey = 'taqweem-language-draft:' + location.pathname;
const draftFields = 'input:not([type=password]):not([autocomplete$="-password"]):not([type=file]):not([type=hidden]), select';
document.querySelectorAll('.password-eye').forEach(button => {
    const input = document.getElementById(button.getAttribute('aria-controls'));
    button.hidden = false;
    button.addEventListener('click', () => {
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(reveal));
    });
});
document.querySelectorAll('[data-language-switch]').forEach(link => link.addEventListener('click', () => {
    if (['ar', 'en'].includes(link.lang)) document.cookie = 'taqweem_ui_locale=' + link.lang + '; Path=/; SameSite=Lax';
    const values = [...document.querySelectorAll(draftFields)].filter(el => el.name && !['diagnosis','prescription','notes','content','reason'].includes(el.name)).map(el=>({name:el.name,value:el.value,checked:el.checked}));
    try { sessionStorage.setItem(draftKey, JSON.stringify({at:Date.now(),values})); } catch {}
}));
try {
    const draft=JSON.parse(sessionStorage.getItem(draftKey)||'null'); sessionStorage.removeItem(draftKey);
    if(draft && Date.now()-draft.at<60000)for(const field of draft.values){
        for(const el of document.querySelectorAll(draftFields))if(el.name===field.name){if(['radio','checkbox'].includes(el.type)){if(el.value===field.value)el.checked=field.checked;}else el.value=field.value;}
    }
} catch {}
// A non-sensitive UI hint lets error templates select a language even when no route middleware ran.
document.cookie = 'taqweem_ui_locale=' + document.documentElement.lang + '; Path=/; SameSite=Lax';
