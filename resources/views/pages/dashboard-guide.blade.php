@extends('layouts.store')

@section('content')
<main class="dashboard-guide-page">
    <section class="page-hero page-hero--premium">
        <div class="container page-hero__layout">
            <div>
                <span class="eyebrow">JANAN / DASHBOARD GUIDE</span>
                <h1>راهنمای کار با<br><em>پنل جانان.</em></h1>
                <p>راهنمای ساده‌ی فیلدها و کارهای روزمره‌ی پنل؛ برای اینکه بدانید هر تغییر کجا دیده می‌شود و هر سفارش چطور جلو می‌رود.</p>
            </div>
            <div class="page-hero__stat"><b>01</b><span>راهنمای استفاده</span></div>
        </div>
    </section>

    <section class="section-block dashboard-guide-section">
        <div class="container">
            <article class="dashboard-guide-content">
                {!! $guideHtml !!}
            </article>
        </div>
    </section>
</main>
@endsection
