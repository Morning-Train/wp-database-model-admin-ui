@php
    /**
     * @var string $title
     * @var array $data
     */
@endphp
<div class="wrap">
    <h1 class="wp-heading-inline">{{ $title }}</h1>
    <hr class="wp-header-end">
    <div class="" style="
background: white;
height: 75vh;
padding: 16px;
border: 1px solid lightgray;
box-shadow: 0 1px 1px rgb(0 0 0 / 4%);
    ">
        @foreach($data as $column => $value)
            <p>{{ $column }}: {{ $value }}</p>
        @endforeach
    </div>
</div>