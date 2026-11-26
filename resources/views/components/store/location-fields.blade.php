@props(['provinceName' => 'shipping_province', 'cityName' => 'shipping_city', 'province' => '', 'city' => ''])

<div class="form-grid__full location-fields" data-location-fields
     data-provinces-url="{{ route('locations.provinces', [], false) }}"
     data-cities-url="{{ route('locations.cities', [], false) }}">
    <label>
        استان
        <input name="{{ $provinceName }}" value="{{ $province }}" maxlength="100" data-location-province-manual autocomplete="address-level1">
        <select name="{{ $provinceName }}" data-location-province autocomplete="address-level1" hidden disabled>
            <option value="">انتخاب استان</option>
        </select>
        @error($provinceName)<small class="location-fields__error">{{ $message }}</small>@enderror
    </label>
    <label>
        شهر
        <input name="{{ $cityName }}" value="{{ $city }}" maxlength="100" data-location-city-manual autocomplete="address-level2">
        <select name="{{ $cityName }}" data-location-city autocomplete="address-level2" hidden disabled>
            <option value="">ابتدا استان را انتخاب کنید</option>
        </select>
        @error($cityName)<small class="location-fields__error">{{ $message }}</small>@enderror
    </label>
    <div class="location-fields__feedback">
        <small data-location-status role="status"></small>
        <button type="button" data-location-retry hidden>تلاش مجدد</button>
    </div>
</div>
