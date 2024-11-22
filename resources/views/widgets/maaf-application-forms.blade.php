<div class="{{-- bg-white p-3 p-md-4 mb-2 mb-md-5 --}}">
    <div class="row">
        @php
            $i = 0;
        @endphp
        @foreach ($forms as $item)
            @php
                $i++;
            @endphp
            <div class="col-12 col-md-6 col-lg-3 mb-3 mb-md-4">
                <div class="card"
                    style="border-radius: 10px; border: 5px #6B3B01 solid; box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;">
                    <div class="card-body">
                        <p class=""
                            style="
                        line-height: 2rem;
                        font-size: 2rem;
                        font-weight: 700;
                        ">
                            <b>{{ $i }}.</b> {{ $item->name }}
                        </p>
                        <hr
                            style="
                        height: 4px;
                        background-color: #6B3B01;
                        margin: 0.5rem 0;
                        ">
                        <p style="line-height: 14px; text-align: justify;">
                            {{ $item->description }}
                        </p>

                        <a href="{{ admin_url('applications') }}/create?create_form_for={{ $item->id }}"
                            class="btn btn-primary btn-block mt-4"
                            style="font-weight: 800;
                        font-size: 2rem;
                        ">APPLY
                            NOW</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<?php

$title = isset($title) ? $title : 'Title';
$number = isset($number) ? $number : '0.00';
$sub_title = isset($sub_title) ? $sub_title : 'Sub-titles';
$link = isset($link) ? $link : 'javascript:;';

if (!isset($is_dark)) {
    $is_dark = true;
}
$is_dark = ((bool) $is_dark);

$bg = '';
$text = 'text-primary';
$text2 = 'text-dark';
if ($is_dark) {
    $bg = 'bg-primary';
    $text = 'text-white';
    $text2 = 'text-white';
}
/* ?>
?>
?>
?>
?>
?>
?>
?>
?>
?><a href="{{ $link }}" class="card {{ $bg }} border-primary mb-4 mb-md-5 "
    style="border-radius: 10px; border: 5px red solid; box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;">
    <div class="card-body py-2 px-2">
        <p class="text-bold mb-2 mb-md-3 {{ $text }} " style="font-weight: 700; font-size: 2.5rem;">
            {{ $title }}</p>
        <p class="display-3  m-0 text-right {{ $text2 }}" style="line-height: 3.2rem">{{ $number }}</p>
        <p class="mt-4 {{ $text2 }}">{{ $sub_title }}</p>
    </div>
</a>
*/
