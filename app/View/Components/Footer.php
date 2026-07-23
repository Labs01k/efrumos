<?php

namespace App\View\Components;

use App\Models\FrontUser;
use App\Models\MenuId;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\View\Component;

class Footer extends Component
{
    public $cookie;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($cookie)
    {
        $this->cookie = $cookie;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $view = 'front.components.footer';

        $footer_menu = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'footer-menu')
            ->with('children')
            ->first();

        $footer_menu_info = MenuId::where('active', 1)
            ->where('deleted', 0)
            ->where('alias', 'footer-menu-info')
            ->with('children')
            ->first();

        return view($view, get_defined_vars());
    }
}
