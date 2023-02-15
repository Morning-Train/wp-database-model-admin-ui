@php
    /**
     * @var bool $hasSideMetaBoxes
     * @var \Morningtrain\WP\DatabaseModelAdminUi\Classes\ModelPage\ModelPage $modelPage
     * @var string $postType
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

    <h1 class="wp-heading-inline">{{ $modelPage->pageTitle }}</h1>

    @if($modelPage->acfCreatePage !== null && current_user_can($modelPage->acfCreatePage->capability))
        <a href="{{ $modelPage->getAcfCreatePageUrl() }}" class="page-title-action aria-button-if-js">{{ __('Add New') }}</a>
    @endif

    <div id="poststuff" class="poststuff">

        <div id="post-body" class="metabox-holder columns-{{ $hasSideMetaBoxes ? '2' : '1' }}">

            @if($hasSideMetaBoxes)
                <div id="postbox-container-1" class="postbox-container">

                    @php(do_meta_boxes($modelPage->pageScreen, 'side', null))

                </div>
            @endif

            <div id="postbox-container-2" class="postbox-container">

                <form id="post" method="get">

                    <input type="hidden" name="page" value="{{ $modelPage->pageSlug }}"/>
                    <input type="hidden" name="post_type" value="{{ $postType }}"/>

                    @if(! empty($modelPage->searchableColumns))
                        {!! $adminTable->search_box($modelPage->searchButtonText, $modelPage->pageSlug) !!}
                    @endif

                    {!! $adminTable->display() !!}

                </form>

                @php(do_meta_boxes($modelPage->pageScreen, 'normal', null))

            </div>

        </div>

        <br class="clear">

    </div>

</div>
