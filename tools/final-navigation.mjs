import fs from 'node:fs';
const p='resources/views/public/dentists.blade.php';let s=fs.readFileSync(p,'utf8');s=s.replace('</x-workspace>',`<section id="services" class="panel" style="scroll-margin-top:2rem"><h2>{{ __('Our Services') }}</h2><p>{{ __('Services offered by the dentists on this page.') }}</p>
@php($doctors->getCollection()->loadMissing('doctor_appointment_types.appointment_type'))
@forelse($doctors as $provider)
@foreach($provider->doctor_appointment_types as $service)<div class="section-heading"><div><h3>{{ $service->appointment_type->name }}</h3><p>{{ $provider->user->name }} · {{ $service->duration_minutes }} {{ __('minutes · Fee') }} {{ number_format($service->price,2) }}</p></div><a class="button" href="{{ route('dentists.show',$provider->id) }}#services">{{ __('View profile') }}</a></div>@endforeach
@empty<p>{{ __('No services are currently listed.') }}</p>@endforelse
</section></x-workspace>`);fs.writeFileSync(p,s);
const q='resources/views/public/dentist.blade.php';fs.writeFileSync(q,fs.readFileSync(q,'utf8').replace('<h2>{{ __(\'Services\') }}</h2>','<h2 id="services">{{ __(\'Services\') }}</h2>'));
for(const lang of ['ar','en']){const p=`lang/${lang}.json`,data=JSON.parse(fs.readFileSync(p,'utf8'));Object.assign(data,lang==='ar'?{'Our Dentists':'أطباؤنا','Our Services':'خدماتنا','Dashboard':'لوحة التحكم','Services offered by the dentists on this page.':'الخدمات المسجّلة لدى الأطباء المعروضين في هذه الصفحة.','Home':'الرئيسية','Care journey':'رحلة الرعاية','Book appointment':'احجز موعداً'}:{'Our Dentists':'Our Dentists','Our Services':'Services','Dashboard':'Dashboard','Services offered by the dentists on this page.':'Services offered by the dentists on this page.'});fs.writeFileSync(p,JSON.stringify(data,null,2)+'\n');}
