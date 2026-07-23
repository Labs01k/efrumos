<div class="sidebar-wrapper" data-simplebar="true">
    <div class="sidebar-header">
        <div>
            <a href="{{ url(LANG, 'back') }}"><img src="{{ asset('admin-assets/images/logo.png') }}" class="logo-icon"
                                                    alt="logo icon"></a>
        </div>
        <div>
            <h4 class="logo-text">Webit CMS</h4>
        </div>
        <div class="toggle-icon ms-auto"><i class='bx bx-arrow-to-left'></i>
        </div>
    </div>

    @if(!empty($menu) && count($menu))

        <ul class="metismenu" id="menu">
            @foreach($menu as $m)
                @if(isset($m->modulesId) && $m->modulesId->alias != 'sitemap' && $m->modulesId->alias != 'modules-constructor')
                    @if(!empty(IfHasChildModules($m->modules_id, LANG_ID, LANG)))
                        <li>
                            <a class="has-arrow" href="javascript:;" aria-expanded="false">
                                <div class="parent-icon"><i class="bx bx-menu"></i></div>
                                <div class="menu-title">{{!empty(IfHasName($m->modules_id, LANG_ID, 'modules')) ? IfHasName($m->modules_id, LANG_ID, 'modules') : trans('variables.another_name')}}</div>
                            </a>
                            <ul class="mm-collapse" style="">
                                <li>
                                    <a class="" href="{{url(LANG.'/back', $m->modulesId->alias)}}" aria-expanded="true"><i
                                                class="bx bx-right-arrow-alt"></i>{{!empty(IfHasName($m->modules_id, LANG_ID, 'modules')) ? IfHasName($m->modules_id, LANG_ID, 'modules') : trans('variables.another_name')}}
                                    </a>
                                </li>
                                {!! IfHasChildModules($m->modules_id, LANG_ID, LANG) !!}
                            </ul>
                        </li>
                    @else
                        <li {{ request()->segment(3) == $m->modulesId->alias ? 'class=mm-active' : '' }}>
                            <a href="{{url(LANG.'/back', $m->modulesId->alias)}}">
                                <div class="parent-icon"><i class="bx bx-right-arrow-alt"></i></div>
                                <div class="menu-title">{{!empty(IfHasName($m->modules_id, LANG_ID, 'modules')) ? IfHasName($m->modules_id, LANG_ID, 'modules') : __('variables.another_name')}}</div>
                            </a>
                        </li>
                    @endif
                @endif
            @endforeach
            @if(auth()->user()->root == 1)
                <li class="menu-label"> {{  __('variables.settings') }}</li>
                @foreach($menu as $m)
                    @if(isset($m->modulesId) && $m->modulesId->alias == 'modules-constructor')
                        <li>
                            <a href="{{url(LANG, ['back', $m->modulesId->alias])}}">
                                <div class="parent-icon"><i class="lni lni-cogs"></i></div>
                                <div class="menu-title">{{$m->name ?? '' }}</div>
                            </a>
                        </li>
                    @endif
                    @if(isset($m->modulesId) && $m->modulesId->alias == 'sitemap')
                        <a href="{{url(LANG, ['back', $m->modulesId->alias])}}" class="sitemap" id="sitemap">
                            <div class="parent-icon"><i class="fadeIn animated bx bx-sitemap"></i></div>
                            <div class="menu-title">{{$m->name ?? '' }}</div>
                        </a>
                    @endif
                @endforeach
            @endif
        </ul>
    @endif
</div>
