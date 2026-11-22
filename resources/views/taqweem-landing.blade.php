@php
    $bookingUrl = !auth()->check() || auth()->user()->role === 'patient'
        ? route('patient.booking')
        : route(auth()->user()->role === 'doctor' ? 'doctor.workspace' : 'admin.dashboard');
    $dashboardUrl = auth()->check() ? route(match(auth()->user()->role) {'doctor'=>'doctor.dashboard', 'admin'=>'admin.dashboard', default=>'patient.dashboard'}) : null;
@endphp
<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head><base href="{{ asset('taqweem-landing') }}/">
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#081e2a"><meta name="description" content="{{ __('TAQWEEM — Find your dentist, book appointments and follow your dental care.') }}">
<title>{{ __('TAQWEEM — A Different Perspective on Dental Care') }}</title>
<link rel="preload" as="image" href="./assets/img/exterior-poster.jpg">
<link rel="stylesheet" href="./css/scan-base.css"><link rel="stylesheet" href="./css/site.css">
<link rel="stylesheet" href="./css/portal.css"><script src="{{ asset('taqweem-integration/site.js') }}" defer></script><script src="./js/portal.js" defer></script><script src="{{ asset('taqweem-integration/scan-engine.js') }}" defer></script>
<link rel="stylesheet" href="{{ asset('css/landing-locale.css') }}"><script>document.cookie = 'taqweem_ui_locale=' + document.documentElement.lang + '; Path=/; SameSite=Lax';window.TaqweemLanding = @js(json_decode(file_get_contents(lang_path(app()->getLocale().'.json')), true));</script></head>
<body data-hero-mode="CONTINUOUS" class="model-pending">
<a class="skip-to-content" href="#approach">{{ __('Skip cinematic intro') }}</a>
<div class="top-progress" aria-hidden="true"><div id="top-progress"></div></div>
<header class="global-header" id="global-header">
  <a class="brand-logo" href="#top" aria-label="{{ __('TAQWEEM — start') }}"><span class="brand-icon" aria-hidden="true"><svg viewBox="0 0 40 40" width="31" height="31" fill="none"><path d="M10 7C5 12 7 20 11 27c2 5 3 8 6 8 3 0 3-8 6-8s3 8 6 8c3 0 4-4 6-8 3-7 5-15 0-20-4-4-10-3-13-1-3-2-9-3-12 1Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M30 9v9M25.5 13.5h9" stroke="currentColor" stroke-width="1.4"/></svg></span><span class="logo-type">{{ __('TAQWEEM') }}<small>{{ __('THE DENTAL EXPERIENCE') }}</small></span></a>
  <nav class="global-nav" aria-label="{{ __('Primary navigation') }}"><a href="#top">{{ __('Home') }}</a><a href="{{ route('dentists.index') }}">{{ __('Our Dentists') }}</a><a href="{{ route('dentists.index') }}#services">{{ __('Our Services') }}</a><a href="#care-process">{{ __('Care journey') }}</a></nav>
  <div class="header-right"><x-language-switch/>
    <div class="landing-account">@guest<a href="{{ route('login') }}">{{ __('Login') }}</a><a href="{{ route('register') }}">{{ __('Register') }}</a>@else<a href="{{ $dashboardUrl }}">{{ __('Dashboard') }}</a><form method="post" action="{{ route('logout.submit') }}">@csrf<button type="submit">{{ __('Sign out') }}</button></form>@endguest</div>
    <a class="nav-book" href="{{ $bookingUrl }}">{{ __('Book appointment') }} <span aria-hidden="true">↗</span></a><button id="nav-toggle" class="nav-toggle" type="button" aria-expanded="false" aria-controls="mobile-nav" aria-label="{{ __('Open navigation') }}"><span></span><span></span></button></div>
  <nav id="mobile-nav" class="mobile-nav" aria-label="{{ __('Mobile navigation') }}" hidden><a href="#top">{{ __('Home') }}</a><a href="{{ route('dentists.index') }}">{{ __('Our Dentists') }}</a><a href="{{ route('dentists.index') }}#services">{{ __('Our Services') }}</a><a href="#care-process">{{ __('Care journey') }}</a>@guest<a href="{{ route('login') }}">{{ __('Login') }}</a><a href="{{ route('register') }}">{{ __('Register') }}</a>@else<a href="{{ $dashboardUrl }}">{{ __('Dashboard') }}</a><form method="post" action="{{ route('logout.submit') }}">@csrf<button type="submit">{{ __('Sign out') }}</button></form>@endguest</nav>
</header>
<main id="top">
<section id="cinema" class="cinema" aria-label="{{ __('Scroll-controlled entrance and clinical journey') }}">
 <div class="cinema-stage" id="cinema-stage">
  <div class="cinema-media" aria-hidden="true">
   <img id="cinema-poster" class="cinema-poster" src="./assets/img/exterior-poster.jpg" alt="" fetchpriority="high">
   <video id="film-outside" class="cinema-video" muted playsinline preload="auto" poster="./assets/img/exterior-poster.jpg" disablepictureinpicture aria-hidden="true"></video>
   <video id="film-inside" class="cinema-video inside-layer" muted playsinline preload="auto" poster="./assets/img/interior-poster.jpg" disablepictureinpicture aria-hidden="true"></video>
   <div class="cinema-overlay"></div><div class="cinema-grain"></div><div class="cinema-mask" id="cinema-mask"></div><div class="cinema-endwash" id="cinema-endwash"></div>
  </div>
  <div class="cinema-coordinate" aria-hidden="true"><span>{{ __('TAQWEEM / ARCHITECTURAL JOURNEY') }}</span><span>{{ __('36° // EVERY DETAIL MATTERS') }}</span></div>
  <div class="cinema-wordmark" id="cinema-intro"><span class="kicker light">{{ __('01 / YOUR JOURNEY BEGINS') }}</span><h1>{{ __('A different') }}<br>{{ __('kind of') }} <em>{{ __('care.') }}</em></h1><p>{{ __('An immersive journey from our doors to the science behind the smile.') }}</p><a href="#approach" class="cinema-link">{{ __('DISCOVER WHAT\'S INSIDE') }} <span>↗</span></a></div>
  <div class="cinema-wordmark cinema-wordmark-next" id="cinema-welcome"><span class="kicker light">{{ __('02 / WELCOME INSIDE') }}</span><h2>{{ __('Welcome to') }}<br><em>{{ __('TAQWEEM.') }}</em></h2><p>{{ __('An experience designed around clarity, comfort and the people behind care.') }}</p></div>
  <div class="cinema-bridge" id="cinema-bridge" aria-hidden="true"><span>{{ __('BEYOND THE ENTRANCE') }}</span><strong>{{ __('Care begins with') }} <em>{{ __('understanding.') }}</em></strong><i></i></div>
  <div class="cinema-bottom"><span class="cinema-hint"><i class="scroll-indicator"></i> {{ __('SCROLL TO ENTER') }}</span><span class="cinema-time" id="cinema-scene-index">01 <i></i> 02</span><a href="#approach" class="cinema-skip">{{ __('SKIP INTRO ↗') }}</a></div>
  <div class="cinema-progress" aria-hidden="true"><div id="cinema-progress-fill"></div></div>
 </div>
</section>


<!-- One brief editorial bridge introduces the care philosophy; no repeat of the opening footage. -->
<section id="approach" class="approach-section section-light" aria-labelledby="approach-title">
 <div class="wrap approach-grid">
  <div class="approach-aside"><span class="kicker ink">{{ __('02 / OUR APPROACH') }}</span><span class="approach-side-note">{{ __('A CONSIDERED WAY TO CARE') }}</span></div>
  <div class="approach-story"><span class="approach-continuity">{{ __('FROM THE DOORWAY TO WHAT MATTERS') }}</span><h2 id="approach-title">{{ __('It starts with') }}<br><em>{{ __('feeling understood.') }}</em></h2><div class="approach-baseline"><p>{{ __('A visit is more than a treatment. It\'s a conversation, a sense of calm, and a clearer understanding of what comes next.') }}</p><a href="#specialists" class="underlined-link">{{ __('MEET THE PEOPLE') }} <span>↗</span></a></div></div>
  <div class="approach-ornament" aria-hidden="true"><svg viewBox="0 0 370 340" fill="none"><path d="M112 35C57 30 20 89 40 159c18 68 41 148 81 148 33 0 26-113 64-113s30 113 63 113c40 0 64-80 81-148C349 89 313 30 258 35c-34 2-52 18-73 18s-38-16-73-18Z" stroke="currentColor" stroke-width="1.6"/><path d="M185 54V194M46 155h277M94 70c45-26 67 13 91 13 27 0 52-40 97-12" stroke="currentColor" stroke-width=".7" stroke-dasharray="5 8"/><circle cx="185" cy="194" r="7" stroke="currentColor"/><circle cx="185" cy="194" r="2" fill="currentColor"/></svg></div>
 </div>
</section>

<section id="specialists" class="specialists section-light">
 <div class="wrap">
  <div class="section-header"><div><span class="kicker ink">{{ __('03 / THE HUMAN CONNECTION') }}</span><h2>{{ __('People make') }}<br><em>{{ __('care personal.') }}</em></h2></div><p>{{ __('Meet the people behind your care. Explore our dentist directory for available clinician profiles and services.') }}</p></div>
  <div class="specialists-editorial">
   <div class="specialists-image"><img src="./assets/img/interior-4.50.jpg" alt="{{ __('Illustrative dental clinic team and hallway') }}" loading="lazy"><div class="vertical-title">{{ __('A BETTER KIND OF EXPERIENCE / TAQWEEM') }}</div><div class="image-count">{{ __('A CALM, CONSIDERED EXPERIENCE') }}</div></div>
   <div class="specialist-list" aria-label="{{ __('Preview of clinical disciplines') }}">
    <button class="specialist-row active" data-specialist="0" type="button" aria-pressed="true"><span>01</span><strong>{{ __('Dental clinicians') }}</strong><span class="row-arrow">↗</span></button>
    <button class="specialist-row" data-specialist="1" type="button" aria-pressed="false"><span>02</span><strong>{{ __('Clinical support') }}</strong><span class="row-arrow">↗</span></button>
    <button class="specialist-row" data-specialist="2" type="button" aria-pressed="false"><span>03</span><strong>{{ __('Patient coordination') }}</strong><span class="row-arrow">↗</span></button>
    <div class="specialist-detail" id="specialist-detail"><span id="specialist-detail-num">{{ __('01 / CARE DISCIPLINE') }}</span><p id="specialist-detail-copy">{{ __('Clinicians guide consultations, explain options and develop appropriate care plans.') }}</p><span class="specialist-disclaimer">{{ __('ILLUSTRATIVE ROLES · VERIFIED PROFILES TO BE CONNECTED') }}</span></div>
    <a href="#visit" class="underlined-link">{{ __('EXPLORE THE VISIT') }} <span>↗</span></a>
   </div>
  </div>
 </div>
</section>


<!-- Standalone dental scan prototype is embedded here as one native section, not an iframe. -->
<section aria-label="{{ __('Interactive treatment-to-tooth cinematic journey') }}" class="journey" id="journey">
<div class="theatre" id="theatre">
<div aria-hidden="true" class="ambient ambient-left"></div><div aria-hidden="true" class="ambient ambient-right"></div>
<div class="film-layer" id="film-layer">
<video aria-hidden="true" class="film" id="treatment-video" loop="" muted="" playsinline="" poster="./assets/img/treatment_poster.jpg" preload="none" data-src="./assets/video/treatment.mp4"></video>
<img alt="" aria-hidden="true" class="film film-still" id="film-still" src="./assets/img/treatment_at_9_5.jpg"/>
<div class="film-vignette"></div>
<div class="film-caption" id="film-caption"><span class="eyebrow pale">{{ __('THE HUMAN MOMENT / 01') }}</span><h2>{{ __('Every detail.') }}<br/><em>{{ __('Every smile.') }}</em></h2><p>{{ __('Behind every confident smile is care that puts people first.') }}</p></div>
<div class="film-corner" id="film-corner"><span>{{ __('TAQWEEM') }}</span><span>{{ __('THE TREATMENT ROOM / CONCEPT') }}</span></div>
</div>
<div aria-hidden="true" class="lens" id="lens"><div class="lens-inner"><span class="bracket tl"></span><span class="bracket tr"></span><span class="bracket bl"></span><span class="bracket br"></span><span class="lens-name">{{ __('FROM THE SCAN TO THE STORY') }}</span></div></div>
<!-- True monitor-anchored, scroll-reversible transition. This is illustrative, not a real X-ray. -->
<div aria-hidden="true" class="portal-gateway" id="portal-gateway">
 <div class="portal-surface" id="portal-surface">
  <img class="portal-reference" src="./assets/img/portal_monitor_match.jpg" alt="" decoding="async">
  <img class="portal-xray" id="portal-xray" src="./assets/img/portal_xray_illustrative.svg" alt="" decoding="async">
  <div class="portal-glass"></div>
 </div>
 <div class="portal-depth" id="portal-depth">
  <div class="portal-perspective-grid" id="portal-perspective-grid"></div>
  <div class="portal-slice" data-depth="0"></div><div class="portal-slice" data-depth="1"></div>
  <div class="portal-slice" data-depth="2"></div><div class="portal-slice" data-depth="3"></div>
  <div class="portal-slice" data-depth="4"></div>
  <div class="portal-depth-ray" id="portal-depth-ray"></div>
  <div class="portal-core" id="portal-core"><img src="./assets/img/tooth_visual_preview.png" alt="" decoding="async"></div>
 </div>
 <div class="portal-filmgrain"></div>
 <div class="portal-metadata"><span>{{ __('TAQWEEM / IMAGING EXPLORATION') }}</span><span>{{ __('ILLUSTRATIVE SCAN · NOT A DIAGNOSTIC IMAGE') }}</span></div>
</div>
<div aria-hidden="true" class="scan-veil" id="scan-veil">
<div class="data-grid"></div><div class="scan-orbit scan-orbit-a"></div><div class="scan-orbit scan-orbit-b"></div><div class="scan-flare"></div>
<div class="scan-readout"><span class="scan-label">{{ __('TAQWEEM / VISUAL EXPLORATION') }}</span><strong>{{ __('Look a little') }} <em>{{ __('closer.') }}</em></strong><small>{{ __('AN ILLUSTRATIVE 3D JOURNEY · NOT A DIAGNOSTIC SCAN') }}</small></div>
<div class="scan-flash" id="scan-flash"></div>
</div>
<div class="world" id="world">
<div aria-hidden="true" class="world-halo"></div><div aria-hidden="true" class="world-grain"></div>
<canvas aria-label="{{ __('Illustrative interactive 3D molar. Drag left and right or use arrow keys to rotate.') }}" id="tooth-canvas" role="img" tabindex="0"></canvas>
<div aria-hidden="true" class="tooth-fallback" id="tooth-fallback"><img alt="" src="./assets/img/tooth_visual_preview.png"/></div>
<div aria-hidden="true" class="scan-plane" id="scan-plane"></div>
<div class="tooth-copy" id="tooth-copy"><span class="eyebrow pale">{{ __('02 / THE REVEAL') }}</span><h2>{{ __('Surface') }}<br/><em>{{ __('revealed.') }}</em></h2><p>{{ __('An illustrative molar emerges from a digital scan environment — then responds to your touch.') }}</p></div>
<div class="scan-copy" id="scan-copy"><span class="eyebrow pale">{{ __('03 / THE SCIENCE') }}</span><h2>{{ __('From stained') }}<br/>{{ __('to') }} <em>{{ __('bright.') }}</em></h2><p>{{ __('Watch the simulated surface transform as the teal scan passes from crown to roots.') }}</p></div>
<div class="services" id="services"><span class="eyebrow pale">{{ __('04 / WHAT COMES NEXT') }}</span><h2>{{ __('Care made') }}<br/><em>{{ __('clear.') }}</em></h2><p>{{ __('Discover services and trusted specialists — shown here as layout concepts, not live clinic data.') }}</p>
<div class="service-list"><div class="service"><span>01</span><div><b>{{ __('Examination') }}</b><small>{{ __('Start with a clearer understanding') }}</small></div></div><div class="service"><span>02</span><div><b>{{ __('Restorative care') }}</b><small>{{ __('Thoughtful treatment planning') }}</small></div></div><div class="service"><span>03</span><div><b>{{ __('Orthodontics') }}</b><small>{{ __('Care tailored to your smile') }}</small></div></div></div>
</div>
<div class="enamel-status" id="enamel-status" aria-live="polite"><span class="enamel-status-dot"></span><span id="enamel-status-text">{{ __('01 / STAINED SURFACE · CONCEPT') }}</span></div>
  <div class="world-footer" id="world-footer"><span>{{ __('ILLUSTRATIVE 3D MOLAR · NOT AN ANATOMICAL DIAGNOSTIC MODEL') }}</span><span>{{ __('DRAG TO ROTATE · SCROLL TO CONTINUE') }}</span></div>
</div>
<div aria-hidden="true" class="chapter-rail"><span>{{ __('THE JOURNEY') }}</span><div class="rail-segments"><i></i><i></i><i></i><i></i></div><span id="chapter-text">{{ __('01 / CARE') }}</span></div>
<div aria-hidden="true" class="side-progress"><i id="side-progress"></i></div>
<div aria-hidden="true" class="gesture-hint" id="gesture-hint">{{ __('SCROLL TO EXPLORE') }} <span>↓</span></div>
<div class="tooth-actions" id="tooth-actions"><button aria-label="{{ __('Reset 3D tooth rotation') }}" id="reset-tooth" type="button">{{ __('RESET VIEW ↺') }}</button></div>
<div aria-live="polite" class="sr-only" id="live-status"></div>
</div>
<div class="reduced-journey wrap" aria-label="{{ __('Technology chapter text alternative') }}"><img src="./assets/img/tooth_visual_preview.png" alt="{{ __('Illustrative 3D molar concept') }}" loading="lazy"><div><span class="kicker light">{{ __('04 / THE TECHNOLOGY EXPERIENCE') }}</span><h2>{{ __('A closer look,') }}<br><em>{{ __('made clearer.') }}</em></h2><p>{{ __('Explore an illustrative view of dental imaging and a simulated molar surface transformation. This visual demonstration does not provide diagnosis.') }}</p><a href="#care-process" class="underlined-link">{{ __('CONTINUE THE JOURNEY') }} <span>↗</span></a></div></div>
</section>


<section id="care-process" class="care-process section-light" aria-labelledby="care-title">
 <div class="wrap"><div class="care-heading"><div><span class="kicker ink">{{ __('05 / THE CARE EXPERIENCE') }}</span><h2 id="care-title">{{ __('Every step') }}<br><em>{{ __('has a purpose.') }}</em></h2></div><p>{{ __('From first conversation to next steps, the experience is designed around understanding — not just technology.') }}</p></div>
 <div class="care-principles"><article><span>{{ __('01 / LISTEN') }}</span><h3>{{ __('Start with a conversation.') }}</h3><p>{{ __('Make space for questions and concerns before discussing possible care.') }}</p></article><article><span>{{ __('02 / EXPLAIN') }}</span><h3>{{ __('Understand your options.') }}</h3><p>{{ __('Use clear visual information to support meaningful conversations with clinicians.') }}</p></article><article><span>{{ __('03 / PLAN') }}</span><h3>{{ __('Move forward with clarity.') }}</h3><p>{{ __('Agree on the next step through the clinic\'s real patient appointment flow.') }}</p></article></div>
 <div class="care-process-bottom"><span>{{ __('TAQWEEM / A THOUGHTFUL EXPERIENCE') }}</span><a href="#visit" class="underlined-link">{{ __('EXPLORE YOUR VISIT') }} <span>↗</span></a></div></div>
</section>

<section id="visit" class="visit-section"><div class="visit-glow"></div><div class="wrap visit-grid"><div><span class="kicker light">{{ __('06 / YOUR NEXT STEP') }}</span><h2>{{ __('Your visit') }}<br><em>{{ __('starts here.') }}</em></h2><p>{{ __('Sign in to request an appointment through your patient workspace. Availability is checked when you submit.') }}</p><div class="visit-steps" aria-label="{{ __('Booking journey') }}"><button type="button" class="visit-step active" data-step="0"><span>01</span> {{ __('Explore care') }}</button><button type="button" class="visit-step" data-step="1"><span>02</span> {{ __('Select your doctor') }}</button><button type="button" class="visit-step" data-step="2"><span>03</span> {{ __('Choose an appointment') }}</button></div><div id="visit-step-message" class="visit-step-message" aria-live="polite">{{ __('Explore the services offered by our dental team.') }}</div><a id="booking-preview" class="main-button" href="{{ $bookingUrl }}">{{ __('BOOK APPOINTMENT') }} <span aria-hidden="true">↗</span></a></div><div class="visit-photo"><img src="./assets/img/interior-7.50.jpg" alt="{{ __('Concept view of modern treatment facilities') }}" loading="lazy"><span>{{ __('TAQWEEM / YOUR EXPERIENCE STARTS HERE') }}</span></div></div></section>


</main>
<footer class="site-footer"><div class="foot-brand">{{ __('TAQWEEM') }}<small>{{ __('THE DENTAL EXPERIENCE') }}</small></div><div><span>{{ __('EXPLORE') }}</span><a href="#approach">{{ __('Our approach') }}</a><a href="#specialists">{{ __('The people') }}</a><a href="#journey">{{ __('3D experience') }}</a><a href="#care-process">{{ __('The care journey') }}</a><a href="{{ $bookingUrl }}">{{ __('Book appointment') }}</a></div><div><span>{{ __('PROJECT NOTE') }}</span><p>{{ __('The cinematic clinic and 3D scenes are illustrative. Dentist profiles and booking are connected to the clinic system.') }}</p></div><div class="foot-bottom">{{ __('© TAQWEEM 2026 · DENTAL CARE EXPERIENCE') }}</div></footer>
<script>
// Integration only: keep all baseline animation files unchanged.
// The base URL resolves asset requests made inside the approved JavaScript.
// Fragment navigation stays in this Laravel document rather than the baseline directory.
document.addEventListener('click', function (event) {
    const link = event.target.closest('a[href^="#"]');
    if (!link) return;
    const fragment = link.getAttribute('href');
    const target = document.getElementById(fragment.slice(1));
    if (target) {
        event.preventDefault();
        target.scrollIntoView({ behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
        history.replaceState(null, '', @js(route('landing')) + fragment);
    }
}, true);
</script></body></html>
