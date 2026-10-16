{{--
    Site_3.docx, картинка 1 (правка 16.10.2026) — переключатель вариантов на
    СОБСТВЕННОЙ странице окислителя (не путать с подбором окислителя ДЛЯ
    краски — та версия теперь строкой внутри set-block.blade.php). Та же
    идея, что переключатель оттенков у краски, но окислитель — не оттенок,
    а две независимые оси (процент и объём), поэтому и контролы разные:

    - Объём — кнопки-пилюли (переиспользует .pb-volume-item, тот же
      компонент, что у обычного выбора объёма volume-select.blade.php),
      только варианты для ТЕКУЩЕГО процента.
    - Процент — select, переход на товар с тем же объёмом у нового процента
      (если есть) или на первый/дешёвый вариант этого процента — сама пара
      процент+объём определяет конкретный товар, поэтому это навигация, а
      не смена значения на месте.

    $goods_item — текущий товар (сам окислитель). Пусто, если это не
    окислитель или в его линейке меньше 2 вариантов.
--}}
@php
    $oxidizer_siblings = \App\Services\Product\OxidizerMatcher::siblingVariants($goods_item);
@endphp
@if($oxidizer_siblings->isNotEmpty())
    @php
        $current_variant = $oxidizer_siblings->firstWhere('is_current', true);
        $current_percent = $current_variant->oxidizer_percent ?? null;
        $current_volume_value = $current_variant->oxidizer_volume_value ?? null;
        $percent_groups = $oxidizer_siblings->groupBy(fn ($one) => $one->oxidizer_percent ?? 'other');
        $volumes_for_current_percent = $percent_groups->get($current_percent, collect());
    @endphp

    @if($volumes_for_current_percent->count() > 1)
        <div class="pb-field pb-volume-field">
            <span class="pb-field-label">{{ trans('variables.product_oxidizer_volume_label') }}</span>
            <div class="pb-volume">
                @foreach($volumes_for_current_percent as $one_variant)
                    <a class="pb-volume-item @if($one_variant->is_current) is-active @endif"
                       href="{{ route('catalog-product', ['product', $one_variant->alias]) }}">
                        {{ $one_variant->oxidizer_volume_label ?? ($one_variant->gramaj . ' ' . trans('variables.ml_short')) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    @if($percent_groups->count() > 1)
        <div class="pb-field pb-field--gap-8">
            <span class="pb-field-label">{{ trans('variables.product_oxidizer_percent_select') }}</span>
            <select class="form-select pb-oxidizer-volume" data-oxidizer-navigate-select>
                @foreach($percent_groups as $percent => $group)
                    @php
                        $target = $group->first(fn ($one) => $one->oxidizer_volume_value === $current_volume_value) ?? $group->first();
                        $vol = is_numeric($percent) ? \App\Services\Product\OxidizerMatcher::volFromPercent((float) $percent) : null;
                    @endphp
                    <option value="{{ route('catalog-product', ['product', $target->alias]) }}"
                            {{ (string) $percent === (string) $current_percent ? 'selected' : '' }}>
                        {{ $percent }}%@if($vol) ({{ $vol }} VOL.)@endif
                    </option>
                @endforeach
            </select>
        </div>
    @endif
@endif
