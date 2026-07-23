<div class="cabinet-menu">
    <ul>
        <li{{ request()->routeIs('cabinet-profile') ? ' class=active' : '' }}>
            <a href="{{ route('cabinet-profile') }}">{{ ShowLabelById(152) }}</a>
        </li>
        <li{{ request()->routeIs('cabinet-orders') ? ' class=active' : '' }}>
            <a href="{{ route('cabinet-orders') }}">{{ ShowLabelById(153) }}</a>
        </li>
        <li{{ request()->routeIs('cabinet-wish') ? ' class=active' : '' }}>
            <a href="{{ route('cabinet-wish') }}">{{ ShowLabelById(154) }}</a>
        </li>
        @if(!$global_user->facebook_id && !$global_user->google_id)
            <li{{ request()->routeIs('cabinet-password') ? ' class=active' : '' }}>
                <a href="{{ route('cabinet-password') }}">{{ ShowLabelById(155) }}</a>
            </li>
        @endif
        <li>
            <a href="{{ route('logout') }}">{{ ShowLabelById(263) }}</a>
        </li>
    </ul>
</div>
