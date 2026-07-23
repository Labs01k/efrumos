<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Meta extends Component
{

    public $meta;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($meta)
    {
        $this->meta = $meta;
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $meta_default = env('APP_NAME');
        $meta_page_img = asset('front-assets/img/share-logo.jpg');

        return view('front.components.meta',compact('meta_default','meta_page_img'));
    }
}
