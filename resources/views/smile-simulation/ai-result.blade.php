@extends('layouts.app')
@section('title', __('Before & After / قبل وبعد'))
@section('content')
<link rel="stylesheet" href="{{ asset('css/smile-ai.css') }}">
<div class="smile-shell" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}"><p>{{ __('smile.notice') }}</p>
<section class="smile-panel"><div class="smile-compare" id="compare"><img src="{{ route('smile.ai.image',[$item->id,'before']) }}" alt="{{ __('Original smile') }}"><img class="smile-after" id="after" src="{{ route('smile.ai.image',[$item->id,'after']) }}" alt="{{ __('Simulated cosmetic smile') }}"></div><label for="comparison">{{ __('Compare / المقارنة') }}</label><input class="smile-range" type="range" id="comparison" min="0" max="100" value="50" aria-label="{{ __('Reveal percentage of simulation') }}"><p><a href="{{ route('smile.ai.download',$item->id) }}">{{ __('Download / تحميل') }}</a> · <a href="{{ route('smile.ai.gallery') }}">{{ __('smile.gallery') }}</a> · <a href="{{ route('smile.ai.create') }}">{{ __('New simulation') }}</a></p></section></div>
<script>const slider=document.getElementById('comparison'),after=document.getElementById('after');slider.addEventListener('input',()=>{after.style.clipPath='inset(0 '+(100-Number(slider.value))+'% 0 0)'});</script>
@endsection
