<option value="" disabled selected>Selectați strada</option>
@if(!empty($streets) && count($streets))
    @foreach($streets as $one_street)
        @if($one_street['street'])
            <option value="{{ $one_street['street'] ?? '' }}">{{ $one_street['type'] ?? '' }}, {{ $one_street['street'] ?? '' }}</option>
        @endif
    @endforeach
@endif
