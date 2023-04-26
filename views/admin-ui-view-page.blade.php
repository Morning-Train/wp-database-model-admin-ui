@php
    /**
     * @var string $title
     * @var bool $showDefaultView
     * @var array $data
     * @var array $columns
     * @var string $pageScreen
     */
@endphp
<style>
    .model-ui-wrap .handle-actions {
        display: none;
    }

    .model-ui-wrap .postbox .hndle {
        cursor: default;
    }

    .model-ui-wrap .wpdbmodeladminui-view-page__content-table {
        margin-bottom: 20px;
    }

    .wpdbmodeladminui-view-page__content table {
        border-bottom: unset;
        background-color: white;
        border-spacing: 0;
        width: 100%;
    }

    .wpdbmodeladminui-view-page__content table th {
        background: #F9F9F9;
        text-align: left;
        vertical-align: top;
        padding: 8px;
        border-bottom: 1px solid #ccd0d4;
        border-right: 1px solid #ccd0d4;
        min-width: 200px;
        width: 30%;
    }

    .wpdbmodeladminui-view-page__content table td {
        padding: 8px;
        border-bottom: 1px solid #ccd0d4;
        min-width: 300px;
    }
</style>

<div class="wrap model-ui-wrap">
    <h1 class="wp-heading-inline">{{ $title }}</h1>
    <hr class="wp-header-end">

    <div id="poststuff" class="wpdbmodeladminui-view-page__content">

        <div id="post-body" class="metabox-holder columns-{{ $hasSideMetaBoxes ? '2' : '1' }}">

            @if($hasSideMetaBoxes)
                <div id="postbox-container-1" class="postbox-container">

                    @php(do_meta_boxes($pageScreen, 'side', null))

                </div>
            @endif

            <div id="postbox-container-2" class="postbox-container">

                @if($showDefaultView)
                    <div class='wpdbmodeladminui-view-page__content-table postbox'>
                        <table>
                            @foreach($data as $column => $value)
                                @php($maybeDecodedValue = json_decode($value))
                                <tr>
                                    <th>{{ ! empty($columns[$column]) ? $columns[$column] . ' (' . $column . ')' : $column }}</th>
                                    <td>
                                        @if(is_string($value) && is_array($maybeDecodedValue) && json_last_error() === JSON_ERROR_NONE)
                                            <pre>{{ print_r($maybeDecodedValue, true) }}</pre>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                @endif

                @php(do_meta_boxes($pageScreen, 'normal', null))

            </div>

        </div>

        <br class="clear">

    </div>
</div>
