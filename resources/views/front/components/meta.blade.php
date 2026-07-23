<meta charset="UTF-8">
<meta name="_token" content="{{ csrf_token() }}">
<meta name="viewport" content="width=device-width, user-scalable=yes, initial-scale=1.0, minimum-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">

<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->

@if(isset($meta->meta_static))
    <title>{{$meta->meta_static ?? $meta_default}}</title>
@else
    @if(isset($meta->itemByLang))
    <meta name="description" content="{{ trim(html_entity_decode($meta->itemByLang->meta_description ? $meta->itemByLang->meta_description : ($meta->itemByLang->body ? substrBySpace($meta->itemByLang->body, 150) : $meta_default), ENT_QUOTES, "UTF-8")) }}">
    <meta name="keywords" content="{{ html_entity_decode(@$meta->itemByLang->meta_keywords ?? $meta_default, ENT_QUOTES, "UTF-8") }}"/>
    <title>{{ html_entity_decode($meta->itemByLang->meta_title ? $meta->itemByLang->meta_title : ($meta->itemByLang->name ? $meta->itemByLang->name : $meta_default), ENT_QUOTES, "UTF-8") }}</title>
    @else
    <title>{{ $meta_default }}</title>
    <meta name="description" content="{{ $meta_default }}"/>
    @endif
@endif
<meta property="og:locale" content="ro_RO"/>
<meta property="og:locale:alternate" content="ru_RU"/>
<meta property="og:type" content="website"/>
<meta property="og:image" content="{{ isset($meta->current_meta_img) ? $meta->current_meta_img : $meta_page_img }}"/>
<meta property="og:url" content="{{ url()->current() }}"/>
<meta property="og:site_name" content="{{ env('APP_DOMAIN')}} "/>
@if(isset($meta->itemByLang))
    <meta property="og:title" content="{{ html_entity_decode($meta->itemByLang->meta_title ? $meta->itemByLang->meta_title : ($meta->itemByLang->name ? $meta->itemByLang->name : $meta_default), ENT_QUOTES, "UTF-8") }}"/>
    <meta property="og:description" content="{{ trim(html_entity_decode($meta->itemByLang->meta_description ? $meta->itemByLang->meta_description : ($meta->itemByLang->body ? substrBySpace($meta->itemByLang->body, 150) : $meta_default), ENT_QUOTES, "UTF-8")) }}"/>
@else
    <meta property="og:title" content="{{ $meta_default }}"/>
    <meta property="og:description" content="{{ $meta_default }}"/>
@endif

<meta property="og:fb:admins" content="{{ env('APP_DOMAIN') }}"/>
<meta name="twitter:card" content="summary"/>
<meta name="twitter:site" content="@url"/>
<meta name="twitter:image" content="{{ isset($meta->current_meta_img) ? $meta->current_meta_img : $meta_page_img }}"/>
@if(isset($meta->itemByLang))
    <meta name="twitter:description" content="{{ trim(html_entity_decode($meta->itemByLang->meta_description ? $meta->itemByLang->meta_description : ($meta->itemByLang->body ? substrBySpace($meta->itemByLang->body, 150) : $meta_default), ENT_QUOTES, "UTF-8")) }}"/>
    <meta name="twitter:title" content="{{ html_entity_decode($meta->itemByLang->meta_title ? $meta->itemByLang->meta_title : ($meta->itemByLang->name ? $meta->itemByLang->name : $meta_default), ENT_QUOTES, "UTF-8") }}"/>
@else
    <meta property="twitter:title" content="{{ $meta_default }}"/>
    <meta property="twitter:description" content="{{ $meta_default }}"/>
@endif
