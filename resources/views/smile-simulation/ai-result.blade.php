@extends('layouts.app')
@section('title','Smile preview')
@section('content')
<link rel="stylesheet" href="{{ asset('css/smile-ai.css') }}">
<main class="smile-shell" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}"><h1>Before & After / قبل وبعد</h1><p>{{ __('smile.notice') }}</p>
<section class="smile-panel"><div class="smile-compare" id="compare"><img src="{{ route('smile.ai.image',[$item->id,'before']) }}" alt="Original smile"><img class="smile-after" id="after" src="{{ route('smile.ai.image',[$item->id,'after']) }}" alt="Simulated cosmetic smile"></div><label for="comparison">Compare / المقارنة</label><input class="smile-range" type="range" id="comparison" min="0" max="100" value="50" aria-label="Reveal percentage of simulation"><p><a href="{{ route('smile.ai.download',$item->id) }}">Download / تحميل</a> · <a href="{{ route('smile.ai.gallery') }}">{{ __('smile.gallery') }}</a> · <a href="{{ route('smile.ai.create') }}">New simulation</a></p></section></main>
<script>const slider=document.getElementById('comparison'),after=document.getElementById('after');slider.addEventListener('input',()=>{after.style.clipPath='inset(0 '+(100-Number(slider.value))+'% 0 0)'});</script>
@endsection
