@props(['name' => 'home'])
<svg {{ $attributes->class(['icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 10h18"/>@break
@case('people')<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3m1-16a3 3 0 0 1 0 6m3 10v-3a6 6 0 0 0-2-4"/>@break
@case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>@break
@case('spark')<path d="m12 2 2.5 7.5L22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5Z"/>@break
@case('list')<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 8h8M8 12h8m-8 4h5"/>@break
@case('tooth')<path d="M12 5C7 0 1 4 4 12c2 7 3 10 5 9 2-1 0-7 3-7s1 6 3 7c2 1 3-2 5-9 3-8-3-12-8-7Z"/>@break
@default<path d="m3 10 9-7 9 7v11h-7v-7h-4v7H3Z"/>
@endswitch
</svg>
