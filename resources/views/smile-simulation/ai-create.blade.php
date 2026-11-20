@extends('layouts.app')
@section('title', __('smile.title'))
@section('content')
<link rel="stylesheet" href="{{ asset('css/smile-ai.css') }}">
<div class="smile-shell" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
 <header class="smile-heading"><div><p class="smile-eyebrow">MEDASSIST</p><h1>{{ __('smile.title') }}</h1><p>{{ __('smile.notice') }}</p></div><a href="{{ route('smile.ai.gallery') }}">{{ __('smile.gallery') }}</a></header>
 @if($errors->any())<div class="smile-alert" role="alert">{{ $errors->first() }}</div>@endif
 <form method="POST" action="{{ route('smile.ai.generate') }}" enctype="multipart/form-data" id="smile-form">@csrf
 <section class="smile-panel"><h2>1. {{ __('smile.upload') }}</h2><label class="smile-drop" for="smile-file" id="smile-drop"><strong>JPEG · PNG · WebP</strong><span>{{ __('smile.drag_drop') }}</span><input type="file" id="smile-file" name="image" accept="image/jpeg,image/png,image/webp" required></label><img id="smile-preview" class="smile-preview" alt="{{ __('smile.preview_alt') }}" hidden><label for="smile-mode">{{ __('smile.photo_type') }}</label><select id="smile-mode" name="mode"><option value="mouth">{{ __('smile.mouth') }}</option><option value="face">{{ __('smile.face') }}</option></select></section>
 <section class="smile-panel"><h2>2. {{ __('smile.treatment') }}</h2><div class="smile-grid">
 @foreach(['whitening'=>__('smile.whitening'),'veneers'=>__('smile.veneers'),'straightening'=>'Teeth Straightening / تقويم الأسنان','comprehensive'=>'Comprehensive Smile / ابتسامة شاملة'] as $key=>$label)
 <label class="smile-treatment"><input type="radio" name="treatment" value="{{ $key }}" {{ $key==='whitening'?'checked':'' }} ><span>{{ $label }}</span><small>SDXL Inpainting</small></label>
 @endforeach</div><label for="smile-strength">{{ __('smile.strength') }}</label><select id="smile-strength" name="strength"><option value="natural">{{ __('smile.natural') }}</option><option value="moderate" selected>{{ __('smile.moderate') }}</option><option value="bright">{{ __('smile.bright') }}</option></select></section>
 <section class="smile-panel"><label class="smile-consent"><input type="checkbox" name="consent" value="1" required><span>{{ __('smile.consent') }}</span></label><button type="submit" id="smile-submit">{{ __('smile.generate') }}</button><p id="smile-status" role="status" aria-live="polite"></p></section>
 </form>
</div>
<script src="{{ asset('js/smile-ai.js') }}" defer></script>
@endsection
