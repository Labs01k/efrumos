<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => 'Необходимо принять :attribute.',
    'active_url'           => ':attribute не является действительным URL.',
    'after'                => ':attribute - это дата после :date.',
    'alpha'                => ':attribute должен состоять только из букв.',
    'alpha_dash'           => 'В :attribute могут содержаться только цифры, буквы и пробелы.',
    'alpha_num'            => 'В :attribute могут содержаться только цифры, буквы.',
    'array'                => ':attribute должен быть массивом.',
    'before'               => ':attribute должен быть датой перед:date.',
    'between'              => [
        'numeric' => 'Значение :attribute  должно быть между :min и :max.',
        'file'    => 'Вес :attribute должен быть между :min и :max килобайт.',
        'string'  => 'Количестов символов :attribute должно быть между :min и :max символов.',
        'array'   => 'Значение  :attribute mдолжно быть между :min и :max значениями.',
    ],
    'boolean'              => 'Поле значения :attribute должно быть истинным или ложным.',
    'confirmed'            => 'Подтверждение :attribute не удалось, значения не совпадают.',
    'date'                 => ':attribute не является действительной датой.',
    'date_format'          => ':attribute не соответсяне соответствует фотмату :format.',
    'different'            => 'Значения :attribute и :other должны отличаться от нынешних.',
    'digits'               => 'Атрибут :attribute должен состоять из :digits .цифр',
    'digits_between'       => 'Значение :attribute должно быть между :min и :max цифрами.',
    'distinct'             => 'В поле :attribute обнаружен дубликат значения.',
    'email'                => 'Атрибут :attribute должен быть действительным email адресом.',
    'exists'               => 'Выбранное значение поля «:attribute» недоступно.',
    'filled'               => 'Заполните поле атрибута :attribute.',
    'image'                => 'Атрибут :attribute должен быть изображением.',
    'in'                   => 'Выбранный атрибут:attribute недействителен.',
    'in_array'             => 'Поля :attribute не существуетт в  :other.',
    'integer'              => 'Атрибут :attribute должен быль целым числом.',
    'ip'                   => 'Атрибут :attribute должен быть действительным  IP адресом',
    'json'                 => 'Атрибут :attribute должен быть действительной строкой значений JSON.',
    'max'                  => [
        'numeric' => 'Значение атрибута :attribute не должно превышать:max.',
        'file'    => 'Вес :attribute не должен превышать :max килобайт.',
        'string'  => ':attribute не должен превышать :max символов.',
        'array'   => ':атрибуту :attribute не должно пренадлежать более :max элементов',
    ],
    'mimes'                => ':attribute должен пренадлежать к типу файлов формата:values.',
    'min'                  => [
        'numeric' => 'Значение атрибута :attribute должно быть не менее :min.',
        'file'    => 'Вес атрибута:attribute должен составлять как минимум :min килобайт.',
        'string'  => 'В атрибуте :attribute должно быть как минимум :min символов.',
        'array'   => 'Атрибуту :attribute должно принадлежать как минимум :min  символов.',
    ],
    'not_in'               => 'Выбранный атрибут :attribute недействителен.',
    'numeric'              => 'Атрибут :attribute должен быть числом.',
    'present'              => 'Должно присутствовать поле атрибута :attribute.',
    'regex'                => 'Неправильный формат атрибута :attribute.',
    'required'             => 'Поле атрибута :attribute обязательно.',
    'required_if'          => 'Заполните поле «:attribute», если :other — :value.',
    'required_unless'      => 'Поле атрибута :attribute обязательно за исключением случаев, когда :other находится в :values.',
    'required_with'        => 'Поле атрибута :attribute обязательно, когда :values присутствует.',
    'required_with_all'    => 'Поле атрибута :attribute обязательно, когда :values присутствует.',
    'required_without'     => 'Поле атрибута :attribute обязательно, когда :values отсутствует.',
    'required_without_all' => 'Поле атрибута :attribute обязательно, когда нет ни одного значения :values .',
    'same'                 => ':attribute и :other должны совпадать.',
    'size'                 => [
        'numeric' => ':attribute должен быть :size.',
        'file'    => ':attribute должен быть :size килобайт.',
        'string'  => 'В атрибуте :attribute должно содержаться :size символов.',
        'array'   => 'Атрибуту :attribute должен содержать :size значений.',
    ],
    'string'               => ' :attribute должен быть строкой символов.',
    'timezone'             => ' :attribute должен быть be a valid zone.',
    'unique'               => ' :attribute уже был использован.',
    'url'                  => 'Неверный формат атрибута :attribute .',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'password' => [
            'regex' => 'Ваш пароль должен содержать минимум 6 символов длиной, содержать одну заглавную букву и число',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'name' => 'Имя',
        'last_name' => 'Фамилия',
        'email' => 'Email',
        'subscribers_email' => 'Email',
        'phone' => 'Телефон',
        'review_text' => 'Коментарий',
        'review' => 'Отзыв',
        'message' => 'Сообщение',
        'subject' => 'Тема',
        'district' => 'Район',
        'city' => 'Город',
        'pickup_shop_id' => 'Магазин самовывоза',
        'delivery_method' => 'Способ доставки',
        'pay_method' => 'Способ оплаты',
        'street' => 'Улица',
        'house' => 'Дом',
        'house_number' => 'Нр. Дома',
        'apartment' => 'Квартира',
        'entrance' => 'Подъезд',
        'address' => 'Адрес',
        'floor' => 'Этаж',
        'intercom_code' => 'Код домофона',
        'password' => 'Пароль',
        'password_confirmation' => 'Повтор. пароль',
        'current_password' => 'Текущий пароль',
        'comment' => 'Сообщение',
        'district_id' => 'Район',
        'agree' => 'Согласен с условиями',
        'rating' => 'Оценка',
        'promocod' => 'Промокод',
        'one_c_code' => '1C код',
    ],

];
