<option value="" disabled selected>Selectați localitate</option>
@if(!empty($localities) && count($localities))
    @foreach($localities as $one_locality)
        <option value="{{ $one_locality['name'] ?? '' }}" data-county-name="{{ $one_locality['county'] ?? '' }}">{{ $one_locality['name'] ?? '' }}</option>
    @endforeach
@endif
