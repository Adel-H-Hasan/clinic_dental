@props(['label','value','hint' => '','icon' => 'calendar'])
<article class="stat-card"><span class="stat-icon"><x-icon :name="$icon"/></span><p>{{ __($label) }}</p><strong>{{ $value }}</strong>@if($hint)<small>{{ __($hint) }}</small>@endif</article>
