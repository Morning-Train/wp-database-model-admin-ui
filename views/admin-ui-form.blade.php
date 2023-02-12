@php
    /**
     * @var bool $hasSideMetaBoxes
     * @var string $pageTitle
     * @var int $page
     * @var bool $useSearchBox
     * @var int $searchBoxText
     * @var string $searchBoxInputId
     * @var \Morningtrain\WP\DatabaseModelAdminUi\Classes\AdminTable $adminTable
     */
@endphp
<style>
    .model-ui-wrap .handle-actions {
        display: none;
    }
    .model-ui-wrap .postbox .hndle {
        cursor: default;
    }
    .model-ui-wrap form#post {
        margin-bottom: 20px;
    }
</style>

<div class="wrap model-ui-wrap">

    <h1 class="wp-heading-inline">{{ $pageTitle }}</h1>

    <div id="poststuff" class="poststuff">

        <div id="post-body" class="metabox-holder columns-{{ $hasSideMetaBoxes ? '2' : '1' }}">

            @if($hasSideMetaBoxes)
                <div id="postbox-container-1" class="postbox-container">

                    @php(do_meta_boxes('toplevel_page_' . $_GET ['page'], 'side', null))

                </div>
            @endif

            <div id="postbox-container-2" class="postbox-container">

                <form id="post" method="post" name="post">

                    <input type="hidden" name="page" value="{{ $page }}"/>

                    @if($useSearchBox)
                        {!! $adminTable->search_box($searchBoxText, $searchBoxInputId) !!}
                    @endif

                    {!! $adminTable->display() !!}

                </form>

                @php(do_meta_boxes('toplevel_page_' . $_GET ['page'], 'normal', null))

            </div>

        </div>

        <br class="clear">

    </div>

</div>
