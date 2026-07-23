<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ShopsId;


class ShopsController extends Controller
{
    public function index()
    {
        $view = 'front.pages.shops.list';

        $segment_2 = request()->segment(2);
        $menu_id = getItemByAlias($segment_2, 'MenuId');

        $shops = ShopsId::where('active', 1)
            ->with('itemByLang')
            ->orderBy('position', 'asc')
            ->get();

        $meta = $menu_id ?? collect([]);

        return view($view, get_defined_vars());
    }
}

