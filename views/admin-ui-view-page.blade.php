@php
    /**
     * @var string $title
     * @var array $data
     * @var array $columns
     */
@endphp
<style>
    .wpdbmodeladminui-view-page__content table {
        border: 1px solid #ccd0d4;
        border-bottom: unset;
        background-color: white;
        border-spacing: 0;
    }

    .wpdbmodeladminui-view-page__content table th {
        background: #F9F9F9;
        text-align: left;
        padding: 8px;
        border-bottom: 1px solid #ccd0d4;
        border-right: 1px solid #ccd0d4;
        min-width: 200px;
    }

    .wpdbmodeladminui-view-page__content table td {
        padding: 8px;
        border-bottom: 1px solid #ccd0d4;
        min-width: 300px;
    }
</style>

<div class="wrap">
    <h1 class="wp-heading-inline">{{ $title }}</h1>
    <hr class="wp-header-end">

    <div id="poststuff" class="wpdbmodeladminui-view-page__content">
        <table>
            @foreach($data as $column => $value)
                <tr>
                    <th>{{ ! empty($columns[$column]) ? $columns[$column] . ' (' . $column . ')' : $column }}</th>
                    <td>{{ $value }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>