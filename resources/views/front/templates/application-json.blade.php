@if(!request()->segment(2))
    <script type="application/ld+json">
            {
                "@context": "http://schema.org",
                "@type": "WebSite",
                "url": "{{ env('APP_URL') }}"
            }
    </script>
@endif

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SearchAction",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "{{ env('APP_URL') }}/{{ $lang }}/catalog?s={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>


@if(request()->segment(2) == 'item')
    <script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Product",
  "description": "{{ $goods_item->body ? strip_tags($goods_item->body) : '' }}",
  "name": "{{ $goods_item->name or '' }}",
  "image": "{{ asset('upfiles/gallery') }}/{{ $goods_item->getPhoto->img or '' }}",
  "sku": "{{ $goods_item->one_c_code or '' }}",
  "offers": {
    "@type": "Offer",
    "availability": "{{ $goods_item->in_stoc == 1 ? "https://schema.org/InStock" : "https://schema.org/OutOfStock" }}",
    "price": "{{ getDefaultPriceFormat($goods_item->price) }}",
    "priceCurrency": "MDL"
  }
}
</script>
@endif

<script type="application/ld+json">
@php
        $third_segment = getItemByAlias(request()->segment(3), 'GoodsSubjectId');
        $fourth_segment = getItemByAlias(request()->segment(4), 'GoodsItemId');
    @endphp
    {
        "@context":"http://schema.org",
        "@type":"BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "item": {
                    "@id": "{{ env('APP_URL') }}/{{ request()->segment(1) }}",
                        "name": "{{ ShowLabelById(85, $lang_id) }}"
                    }
                }
                @if(request()->segment(3))
        ,{
            "@type": "ListItem",
            "position": 2,
            "item": {
                "@id": "{{ env('APP_URL') }}/{{ request()->segment(1) }}/catalog/{{ $third_segment->alias ?? '' }}",
                            "name": "{{ $third_segment->name ?? '' }}"
                        }
                    }
                @endif
    @if(request()->segment(4))
        ,{
            "@type": "ListItem",
            "position": 3,
            "item": {
                "@id": "{{ env('APP_URL') }}/{{ request()->segment(1) }}/catalog/{{ $third_segment->alias ?? '' }}/{{ $fourth_segment->alias ?? '' }}",
                            "name": "{{ $fourth_segment->name ?? '' }}"
                        }
                    }
                @endif
    ]
 }
</script>
