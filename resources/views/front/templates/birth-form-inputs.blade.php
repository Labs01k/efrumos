<div class="form-item">
    <label for="birth_day" class="sr-only">{{ ShowLabelById(38) }}</label>
    <select name="birth_day" id="birth_day">
        <option value="">{{ ShowLabelById(45) }}</option>
        @for($d = 1; $d <= 31; $d++)
            <option value="{{ $d }}" {{ $global_user && $global_user->birth && \Carbon\Carbon::parse($global_user->birth)->format('d') == $d ? 'selected' : '' }} {{ $global_user && $global_user->birth ? 'disabled' : '' }}>{{ $d }}</option>
        @endfor
    </select>
</div>
<div class="form-item">
    <label for="birth_month" class="sr-only">{{ ShowLabelById(39) }}</label>
    <select name="birth_month" id="birth_month">
        <option value="">{{ ShowLabelById(45) }}</option>
        @foreach($month_list as $one_month)
            <option value="{{ $loop->iteration }}" {{ $global_user && $global_user->birth && \Carbon\Carbon::parse($global_user->birth)->format('m') == $loop->iteration ? 'selected' : '' }} {{ $global_user && $global_user->birth ? 'disabled' : '' }}>{{ $one_month? ucfirst($one_month) : '' }}</option>
        @endforeach
    </select>
</div>
<div class="form-item">
    <label for="birth_year" class="sr-only">{{ ShowLabelById(40) }}</label>
    <select name="birth_year" id="birth_year">
        <option value="">{{ ShowLabelById(45) }}</option>
        @for($y = 1; $y < 80; $y++)
            <option value="{{ \Carbon\Carbon::now()->format('Y')-$y }}" {{ $global_user && $global_user->birth && \Carbon\Carbon::parse($global_user->birth)->format('Y') == \Carbon\Carbon::now()->format('Y')-$y? 'selected' : '' }} {{ $global_user && $global_user->birth ? 'disabled' : '' }}>{{ \Carbon\Carbon::now()->format('Y')-$y }}</option>
        @endfor
    </select>
</div>
