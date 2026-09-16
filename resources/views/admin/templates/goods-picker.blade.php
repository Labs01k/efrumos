{{--
    Мультиселект товаров с поиском на сервере (select2 ajax, инициализация —
    [data-goods-picker] в admin-assets/js/custom.js). В разметке только уже
    выбранные товары: весь каталог в форму не грузится.

    Параметры:
      $name       — имя поля без [] (produse_similare, goods_list)
      $label      — подпись
      $selected   — выбранные товары, GoodsPicker::byIds(); у рекомендаций
                    ещё picker_reason — почему товар не показывается на сайте
      $search_url — адрес поиска, ответ в формате GoodsPicker::results()
      $exclude_id — товар, который не предлагать (сам редактируемый), или null
      $max        — сколько можно выбрать, или null
      $hint       — подсказка под полем, или null
--}}
@php
    $picker_lang = [
        'searching' => __('variables.recommendations_select_searching'),
        'noResults' => __('variables.recommendations_select_no_results'),
        'loadingMore' => __('variables.recommendations_select_loading_more'),
        'errorLoading' => __('variables.recommendations_select_error'),
        'maximumSelected' => $max ? __('variables.recommendations_select_maximum', ['max' => $max]) : '',
    ];
@endphp
<label for="{{ $name }}" class="form-label">{{ $label }}</label>
<select class="form-select" name="{{ $name }}[]" id="{{ $name }}" multiple
        data-goods-picker
        data-url="{{ $search_url }}"
        data-exclude="{{ $exclude_id }}"
        data-max="{{ $max }}"
        data-lang="{{ json_encode($picker_lang) }}">
    @foreach($selected as $one_goods)
        <option value="{{ $one_goods->id }}" selected
                data-reason="{{ $one_goods->picker_reason ?? '' }}">{{ \App\Services\Admin\GoodsPicker::label($one_goods) }}</option>
    @endforeach
</select>
@if(!empty($hint))
    <div class="form-text">{{ $hint }}</div>
@endif
