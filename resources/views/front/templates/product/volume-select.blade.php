{{--
    Выбор объёма. Макет Figma: нода 785:11827 (default / hover / другой вариант / один вариант).
    Ожидает $volumes — коллекцию GoodsItemId той же линейки с разным объёмом.

    Окислители исключены (правка 16.10.2026, Site_3.docx картинка 1): у них
    объём и так показывает oxidizer-picker.blade.php, но вместе с процентом
    (та же пара значений определяет товар) — этот универсальный селектор
    про процент не знает и предлагал бы переключиться на другой процент под
    видом смены объёма.
--}}
@if(!empty($volumes) && count($volumes) && !\App\Services\Product\OxidizerMatcher::isOxidizer($goods_item))
    <div class="pb-field pb-volume-field">
        <span class="pb-field-label">{{ trans('variables.product_volume') }}</span>
        <div class="pb-volume">
            @foreach($volumes as $one_volume)
                <a class="pb-volume-item @if($one_volume->id === $goods_item->id) is-active @endif"
                   href="{{ route('catalog-product', ['product', $one_volume->alias]) }}">
                    {{ $one_volume->gramaj }}
                </a>
            @endforeach
        </div>
    </div>
@endif
