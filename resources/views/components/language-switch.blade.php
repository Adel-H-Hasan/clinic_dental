@props(['currentLocale' => app()->getLocale()])
<nav class="language-switch" aria-label="{{ __('Language', [], $currentLocale) }}">
@foreach(['en'=>'EN','ar'=>'العربية'] as $locale=>$label)
<a href="{{ route('language.switch', ['locale'=>$locale, 'return'=>request()->getRequestUri()]) }}" lang="{{ $locale }}" data-language-switch @if($currentLocale===$locale) aria-current="true" @endif>{{ $label }}</a>
@endforeach
</nav>
