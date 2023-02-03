@php
    /**
     * @var string $title
     * @var array $data
     * @var WP_Screen $screen
     */
@endphp
<div class="wrap">
    <h1 class="wp-heading-inline">{{ $title }}</h1>
    <hr class="wp-header-end">
    <div class="">
        @foreach($data as $column => $value)
            <p>{{ $column }}: {{ $value }}</p>
        @endforeach
    </div>

    @php
        do_meta_boxes($screen, 'side', '')
    @endphp
</div>