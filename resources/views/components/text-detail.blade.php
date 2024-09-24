@php
    $title = $t ?? '';
    $value = $v ?? '';

    if (strlen($value) < 1) {
        $value = '-';
    }
    $title = strtoupper($title);
@endphp
<p class="p-0 m-0">
    <span class="title">{{ $title }}:</span> {{ $value }}
</p>
