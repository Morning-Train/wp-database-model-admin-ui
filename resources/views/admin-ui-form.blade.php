@php
    /**
     * @var string $pageTitle
     * @var int $page
     * @var int $searchBoxText
     * @var string $searchBoxInputId
     * @var \Morningtrain\WP\DatabaseModelAdminUi\Model\AdminTable $adminTable
     */
@endphp
<div class="wrap">
    <h1 class="wp-heading-inline">{{ $pageTitle }}</h1>

    <hr class="wp-header-end">

    <form method="get">
        <input type="hidden" name="page" value="{{ $page }}" />

        {!! $adminTable->search_box($searchBoxText, $searchBoxInputId) !!}

        {!! $adminTable->display() !!}
    </form>
</div>