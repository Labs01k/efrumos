<?php

namespace App\Exports;

use App\Models\GoodsItemId;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class GoodsExport implements FromQuery, WithHeadings,WithMapping, WithChunkReading
{
    public function query()
    {
        return GoodsItemId::query()
            ->where('active', 1)
            ->where('deleted', 0)
            ->where('in_stoc', 1)
            ->leftJoin('goods_item as goods_ro', function ($join) {
                $join->on('goods_ro.goods_item_id', '=', 'goods_item_id.id')
                    ->where('goods_ro.lang_id', 2);
            })
            ->leftJoin('goods_item as goods_ru', function ($join) {
                $join->on('goods_ru.goods_item_id', '=', 'goods_item_id.id')
                    ->where('goods_ru.lang_id', 3);
            })
            ->with(['oImage', 'getSubjectId'])
            ->select([
                'goods_item_id.*',
                'goods_ro.name as name_ro',
                'goods_ru.name as name_ru',
                'goods_ro.body as description_ro',
                'goods_ru.body as description_ru',
            ])
            ->orderBy('goods_subject_id', 'asc')
            ->orderBy('products_count', 'desc');
    }

    public function map($item): array
    {
        return [
            'Publicat' => 'DA',
            'Nume' => $item->name_ro ?? '',
            'Название_RU' => $item->name_ru ?? '',
            'Cantitate disponibila' => (string) ($item->products_count ?? 0),
            'Imagine pentru anunt' => optional($item->oImage)->img ? asset('upfiles/goods-items/m/' . showImg($item->oImage->img)) : '',
            'Pret' => $item->price ?? 0,
            'Цена со скидкой' => $item->price_promo ?? 0,
            'Unitatea de măsură de bază' => '',
            'Единица измерения' => '',
            'Sectiune' => $item->getSubjectId->section ?? $item->getSubjectId->name,
            'Imagine detaliata' => optional($item->oImage)->img ? asset('upfiles/goods-items/' . $item->oImage->img) : '',
            'Descriere detaliata' =>  $item->description_ro ?? '',
            'Полное описание_RU' => $item->description_ru ?? '',
            'Articol' => $item->articol ?? '',
            'SKU' => $item->one_c_code ?? '',
            'Culoare' => '',
            'Tipul De Păr' => ''
        ];
    }
    public function headings(): array
    {
        return [
            'Publicat',
            'Nume',
            'Название_RU',
            'Cantitate disponibila',
            'Imagine pentru anunt',
            'Pret',
            'Цена со скидкой',
            'Unitatea de măsură de bază',
            'Единица измерения',
            'Sectiune',
            'Imagine detaliata',
            'Descriere detaliata',
            'Полное описание_RU',
            'Articol',
            'SKU',
            'Culoare',
            'Tipul De Păr'
        ];
    }

    public function chunkSize(): int
    {
        return 500;
    }
    /*public function array(): array
    {
        $result = [];

        $goods = GoodsItemId::query()
            ->leftJoin('goods_item as goods_ro', function ($join) {
                $join->on('goods_ro.goods_item_id', '=', 'goods_item_id.id')
                    ->where('goods_ro.lang_id', 2);
            })
            ->leftJoin('goods_item as goods_ru', function ($join) {
                $join->on('goods_ru.goods_item_id', '=', 'goods_item_id.id')
                    ->where('goods_ru.lang_id', 3);
            })
            ->with(['oImage', 'getSubjectId'])
            ->select([
                'goods_item_id.*',
                'goods_ro.name as name_ro',
                'goods_ru.name as name_ru',
                'goods_ro.body as description_ro',
                'goods_ru.body as description_ru',
            ])
            ->orderBy('goods_subject_id', 'asc')
            ->orderBy('products_count', 'desc')
            ->get();

        if ($goods->isEmpty()) {
            return [];
        }

        foreach ($goods as $item) {

            $result[] = [
                'Publicat' => 'DA',
                'Nume' => $item->name_ro ?? '',
                'Название_RU' => $item->name_ru ?? '',
                'Cantitate disponibila' => (string) ($item->products_count ?? 0),
                'Imagine pentru anunt' => optional($item->oImage)->img ? asset('upfiles/goods-items/m/' . showImg($item->oImage->img)) : '',
                'Pret' => $item->price ?? 0,
                'Цена со скидкой' => $item->price_promo ?? 0,
                'Unitatea de măsură de bază' => '',
                'Единица измерения' => '',
                'Sectiune' => $item->getSubjectId->section ?? $item->getSubjectId->name,
                'Imagine detaliata' => optional($item->oImage)->img ? asset('upfiles/goods-items/' . $item->oImage->img) : '',
                'Descriere detaliata' =>  $item->description_ro ?? '',
                'Полное описание_RU' => $item->description_ru ?? '',
                'Articol' => $item->articol ?? '',
                'SKU' => $item->one_c_code ?? '',
                'Culoare' => '',
                'Tipul De Păr' => ''
            ];
        }

        return $result;
    }*/
}
