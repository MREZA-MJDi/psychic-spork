@props([
    'sort' => 'newest',
    'perPage' => 12,
])

<form class="catalog-inline-sort" method="GET" action="{{ url()->current() }}">
    <label>
        <span>مرتب‌سازی</span>
        <select name="sort" onchange="this.form.submit()">
            <option value="newest" @selected($sort === 'newest')>جدیدترین</option>
            <option value="oldest" @selected($sort === 'oldest')>قدیمی‌ترین</option>
            <option value="price_asc" @selected($sort === 'price_asc')>ارزان‌ترین</option>
            <option value="price_desc" @selected($sort === 'price_desc')>گران‌ترین</option>
            <option value="name_asc" @selected($sort === 'name_asc')>نام: الف تا ی</option>
            <option value="name_desc" @selected($sort === 'name_desc')>نام: ی تا الف</option>
        </select>
    </label>

    <label>
        <span>نمایش</span>
        <select name="per_page" onchange="this.form.submit()">
            <option value="12" @selected($perPage === 12)>۱۲</option>
            <option value="24" @selected($perPage === 24)>۲۴</option>
            <option value="36" @selected($perPage === 36)>۳۶</option>
        </select>
    </label>
</form>
