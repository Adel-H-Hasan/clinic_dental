@foreach(['success','status'] as $key)
@if(session($key))<div role="status" class="notice success">{{ __(session($key)) }}</div>@endif
@endforeach
@if($errors->any())<div role="alert" class="notice error" tabindex="-1" data-errors><strong>{{ __('Please check the following') }}</strong><ul>@foreach($errors->all() as $error)<li>{{ __($error) }}</li>@endforeach</ul></div>@endif
