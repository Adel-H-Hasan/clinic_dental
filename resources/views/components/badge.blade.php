@props(['status'])
<span class="badge {{ in_array($status,['pending','confirmed','completed','cancelled','draft','approved']) ? $status : '' }}">{{ __(ucfirst($status)) }}</span>
