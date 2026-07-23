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

    'accepted'             => 'Atributul :attribute trebuie acceptat.',
    'active_url'           => 'Atributul :attribute nu e un URL valid.',
    'after'                => 'Atributul :attribute trebuie să fie o dată după data :date.',
    'alpha'                => 'Atributul :attribute poate conține doar litere.',
    'alpha_dash'           => 'Atributul :attribute poate conține doar litere, cifre și cratime.',
    'alpha_num'            => 'Atributul :attribute poate conține doar litere și cifre.',
    'array'                => 'Atributul :attribute trebuie să fie un masiv.',
    'before'               => 'Atributul :attribute trebuie să fie o dată pînă la data :date.',
    'between'              => [
        'numeric' => 'Atributul :attribute trebuie să se cuprindă între :min și :max.',
        'file'    => 'Atributul :attribute trebuie să se cuprindă între :min și :max kilobiți.',
        'string'  => 'Atributul :attribute trebuie să se cuprindă între :min și :max caractere.',
        'array'   => 'Atributul :attribute trebuie să se cuprindă între :min și :max itemi.',
    ],
    'boolean'              => 'Cîmpul atributului :attribute trebuie să fie adevărat sau fals.',
    'confirmed'            => 'Confirmarea atributuului :attribute nu se potrivește.',
    'date'                 => 'Atributul :attribute nu e o dată validă.',
    'date_format'          => 'Atributul :attribute nu are formatul :format.',
    'different'            => 'Atributul :attribute și :other trebuie să fie diferite.',
    'digits'               => 'Atributul :attribute trebuie să conțină :digits cifre.',
    'digits_between'       => 'Atributul :attribute trebuie să fie între numerele :min și :max',
    'distinct'             => 'Cîmpul atributuului :attribute are o valoare duplicat.',
    'email'                => 'Atributul :attribute trebuie să aibă un email valid.',
    'exists'               => 'Atributul selectat :attribute  nu e valid.',
    'filled'               => 'Cîmpul :attribute este obligatoriu.',
    'image'                => 'Atributul :attribute trebuie să fie o imagine.',
    'in'                   => 'Atributul selectat :attribute  nu e valid.',
    'in_array'             => 'Cîmpul atributuului :attribute nu există în :other.',
    'integer'              => 'Atributul :attribute trebuie să fie un număr întreg.',
    'ip'                   => 'Atributul :attribute trebuie să fie o adresă IP validă.',
    'json'                 => 'Atributul :attribute trebuie să fie un JSON valid.',
    'max'                  => [
        'numeric' => ':attribute nu poate fi mai mare decît :max.',
        'file'    => ':attribute nu poate fi mai mare decît :max kilobiți.',
        'string'  => ':attribute nu poate fi mai mare decît :max caractere.',
        'array'   => ':attribute nu poate avea mai mult decît :max itemi.',
    ],
    'mimes'                => ':attribute trebuie să fie un fișier de tipul: :values.',
    'min'                  => [
        'numeric' => 'attribute trebuie să fie cel puțin :min.',
        'file'    => ':attribute trebuie să fie cel puțin :min kilobiți.',
        'string'  => ':attribute trebuie să fie cel puțin :min caractere.',
        'array'   => ':attribute trebuie să aibă cel puțin :min itemi.',
    ],
    'not_in'               => 'Atributul selectat :attribute nu e valid.',
    'numeric'              => ':attribute trebuie să fie număr.',
    'present'              => 'Cîmpul atributuului :attribute trebuie să fie prezent.',
    'regex'                => 'Formatul atributuului :attribute nu e valid.',
    'required'             => 'Cîmpul atributului :attribute este obligatoriu.',
    'required_if'          => 'Cîmpul atributului :attribute este obligatoriu cînd :other este :value.',
    'required_unless'      => 'Cîmpul atributului :attribute este obligatoriu dacă :other nu este în :values.',
    //'required_with'        => 'Cîmpul atributului :attribute este obligatoriu cînd :values este prezent.',
    'required_with'        => 'Cîmpul atributului :attribute este obligatoriu.',
    'required_with_all'    => 'Cîmpul atributului :attribute este obligatoriu cînd :values este prezent.',
    'required_without'     => 'Cîmpul atributului :attribute este obligatoriu cînd :values nu este prezent.',
    'required_without_all' => 'Cîmpul atributului :attribute este obligatoriu cînd nici una din valori :values nu sunt prezente.',
    'same'                 => ':attribute și :other trebuie să se potrivească.',
    'size'                 => [
        'numeric' => 'Cîmpul atributului :attribute trebuie să fie egal cu :size.',
        'file'    => 'Cîmpul atributului :attribute trebuie să fie egal cu :size kilobiți.',
        'string'  => 'Cîmpul atributului :attribute trebuie să fie egal cu :size caractere.',
        'array'   => 'Cîmpul atributului :attribute trebuie să conțină :size itemi.',
    ],
    'string'               => 'Cîmpul atributului :attribute trebuie să fie un șir de caractere.',
    'timezone'             => 'Cîmpul atributului :attribute trebuie să fie o zonă validă.',
    'unique'               => 'Cîmpul atributului :attribute există deja.',
    'url'                  => 'Cîmpul atributului :attribute nu are un format valid.',

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
            'regex' => 'Parola dvs. să conțină cel puțin 6 caractere  și să conțină o literă mare și cel puțin un număr',
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
        'name' => 'Prenume',
        'last_name' => 'Nume',
        'email' => 'Email',
        'subscribers_email' => 'Email',
        'phone' => 'Telefon',
        'review_text' => 'Comentariu',
        'review' => 'Recenzie',
        'message' => 'Mesaj',
        'subject' => 'Subiect',
        'district' => 'Raion',
        'city' => 'Oraș',
        'street' => 'Stradă',
        'house' => 'Casă',
        'house_number' => 'Nr. Casă',
        'apartment' => 'Apartament',
        'entrance' => 'Scara',
        'floor' => 'Etajul',
        'address' => 'Adresa',
        'intercom_code' => 'Cod interfon',
        'password' => 'Parola',
        'password_confirmation' => 'Repeta parola',
        'current_password' => 'Parola actuală',
        'comment' => 'Mesaj',
        'district_id' => 'Raionul',
        'agree' => 'Sunt de acord',
        'rating' => 'Evaluează',
        'promocod' => 'Promo cod',
        'one_c_code' => 'Cod 1C',
    ],

];
