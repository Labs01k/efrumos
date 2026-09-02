{{--
    Выбор объёма. Макет Figma: нода 785:11827 (default / hover / другой вариант / один вариант).
    Ожидает $volumes — коллекцию GoodsItemId той же линейки с разным объёмом.
--}}
@if(!empty($volumes) && count($volumes))
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
