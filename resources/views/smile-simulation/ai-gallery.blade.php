@extends('layouts.app')
@section('title', __('smile.gallery'))
@section('content')
<link rel="stylesheet" href="{{ asset('css/smile-ai.css') }}">
<main class="smile-shell" dir="{{ app()->getLocale()==='ar'?'rtl':'ltr' }}"><h1>{{ __('smile.gallery') }}</h1>
<p>{{ __('smile.notice') }}</p><a href="{{ route('smile.ai.create') }}">{{ __('smile.generate') }}</a>
<div class="smile-grid">@forelse($items as $item)
<a class="smile-panel" href="{{ route('smile.ai.show',$item->id) }}"><img style="max-width:100%;height:180px;object-fit:cover" src="{{ route('smile.ai.image',[$item->id,'after']) }}" alt="Simulation"><p>{{ $item->treatment }} — {{ $item->created_at }}</p></a>
@empty <p>No previews in this browser session / لا توجد نتائج في هذه الجلسة</p> @endforelse</div></main>
@endsection
