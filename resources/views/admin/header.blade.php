<header>
    <div class="topbar d-flex align-items-center">
        <nav class="navbar navbar-expand">
            <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
            </div>
            <div class="top-menu ms-auto">
                @if(!empty($lang_list) && count($lang_list))
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="lang-switch"
                                data-bs-toggle="dropdown" aria-expanded="false">
                            {{ strtoupper($lang) }}
                        </button>
                        <ul class="dropdown-menu lang-dropdown-menu" aria-labelledby="lang-switch">
                            @foreach($lang_list as $one_lang)
                                <li><a class="dropdown-item{{ $lang == $one_lang ? ' active' : '' }}" href="{{ count(request()->segments()) > 0 ? str_replace('/'.$lang, '/'.$one_lang, request()->fullUrl()) : url($one_lang) }}">{{ Str::upper($one_lang) }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <a href="{{url('/', LANG)}}" target="_blank"
               class="btn btn-sm btn-outline-success mt-lg-0 btn-go-home"><i
                        class="lni lni-website"></i>{{ __('variables.go_to_the_site') }}</a>
            <div class="user-box dropdown">
                <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret"
                   href=""
                   role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if(!empty(auth()->user()->img))
                        <img src="{{asset("upfiles/admin-user/s/" . auth()->user()->img)}}" class="user-img"
                             alt="{{auth()->user()->name}}" title="{{auth()->user()->name}}">
                    @else
                        <img src="{{ asset('admin-assets/images/profile.png') }}" class="user-img" alt="user avatar">
                    @endif

                    <div class="user-info ps-3">
                        <p class="user-name mb-0">{{auth()->user()->name}}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item"
                           href="{{ $user_settings_url }}"><i
                                    class="bx bx-cog"></i><span>{{ __('variables.settings') }}</span></a>
                    </li>
                    <li>
                        <div class="dropdown-divider mb-0"></div>
                    </li>
                    <li><a class="dropdown-item" href="{{url(LANG . '/back/auth/logout')}}"><i
                                    class='bx bx-log-out-circle'></i><span>{{ __('variables.log_out') }}</span></a>
                    </li>
                </ul>
            </div>
        </nav>
    </div>
</header>
