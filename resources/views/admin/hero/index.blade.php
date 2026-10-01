@extends('layouts.admin')

@section('title', 'Hero صفحه اصلی')
@section('page-title', 'Hero صفحه اصلی')

@section('content')
<div class="admin-page hero-admin-page">
    <div class="admin-page-head">
        <div>
            <span class="eyebrow">STORE / HERO</span>
            <h1>انتخاب تصاویر Hero</h1>
            <p>فقط محصولاتی که تیک می‌زنی در Hero صفحه اصلی نمایش داده می‌شوند. ترتیب انتخاب، ترتیب نمایش است.</p>
        </div>
        <div class="admin-page-head__meta">
            <strong>{{ $selectedIds->count() }}/90</strong>
            <span>اسلاید فعال</span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.hero.index') }}" class="admin-filter-bar" style="margin-bottom:16px">
        <input
            type="search"
            name="q"
            value="{{ $search }}"
            placeholder="جستجوی محصول یا برند…"
            aria-label="جستجوی محصول یا برند"
        >
        <button class="button button--ghost" type="submit">جستجو</button>
        @if($search !== '')
            <a class="button button--ghost" href="{{ route('admin.hero.index') }}">پاک کردن</a>
        @endif
    </form>

    <form method="POST" action="{{ route('admin.hero.update') }}" data-hero-form>
        @csrf
        @method('PUT')

        <div class="hero-admin-grid">
            @foreach($products as $product)
                @php
                    $checked = $selectedIds->contains($product->id);
                    $image = $product->primaryGalleryMedia?->url ?: $product->brand?->logoMedia?->url;
                @endphp

                <label class="hero-product-card {{ $checked ? 'is-selected' : '' }}">
                    <input
                        type="checkbox"
                        name="product_ids[]"
                        value="{{ $product->id }}"
                        @checked($checked)
                        data-hero-product
                    >
                    <span class="hero-product-card__check">✓</span>

                    <span class="hero-product-card__image">
                        @if($image)
                            <img src="{{ $image }}" alt="{{ $product->name }}" loading="lazy">
                        @else
                            <span>بدون تصویر</span>
                        @endif
                    </span>

                    <span class="hero-product-card__body">
                        <strong>{{ $product->name }}</strong>
                        <small>{{ $product->brand?->name ?? 'بدون برند' }}</small>
                    </span>
                </label>
            @endforeach
        </div>

        @if($products->hasPages())
            <nav class="store-pagination" aria-label="صفحه‌بندی انتخاب Hero" style="margin-top:18px">
                {{ $products->onEachSide(1)->links() }}
            </nav>
        @endif

        <div class="hero-admin-hidden" data-hero-hidden></div>

        <div class="hero-admin-actions">
            <span>حداکثر ۹۰ محصول انتخاب کن.</span>
            <button class="button button--primary" type="submit">ذخیره Hero</button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
 .hero-admin-hidden{display:none}.hero-admin-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px}
.hero-product-card{position:relative;display:flex;flex-direction:column;gap:10px;padding:10px;border:1px solid var(--admin-border,#ddd);border-radius:18px;background:var(--admin-surface,#fff);cursor:pointer}
.hero-product-card input{position:absolute;opacity:0;pointer-events:none}
.hero-product-card__image{display:block;aspect-ratio:1/1;overflow:hidden;border-radius:13px;background:#f2f2f2}
.hero-product-card__image img{width:100%;height:100%;object-fit:cover}
.hero-product-card__body{display:flex;flex-direction:column;gap:4px}
.hero-product-card__body small{opacity:.65}
.hero-product-card__check{position:absolute;top:16px;right:16px;width:30px;height:30px;border-radius:50%;display:grid;place-items:center;background:#fff;border:1px solid #ddd;color:transparent;z-index:2}
.hero-product-card.is-selected{border-color:#111;box-shadow:0 0 0 2px #1112}
.hero-product-card.is-selected .hero-product-card__check{background:#111;color:#fff;border-color:#111}
.hero-admin-actions{position:sticky;bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:18px;padding:14px 16px;border-radius:16px;background:var(--admin-surface,#fff);border:1px solid var(--admin-border,#ddd);box-shadow:0 10px 30px #0001}
@media(max-width:600px){.hero-admin-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.hero-product-card{padding:8px;border-radius:14px}.hero-admin-actions{align-items:stretch;flex-direction:column}}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded',function(){
    const boxes=[...document.querySelectorAll('[data-hero-product]')];
    const form=document.querySelector('[data-hero-form]');
    const hidden=document.querySelector('[data-hero-hidden]');
    const key='janan:admin:hero:selected:v1';
    let selected=new Set(@json($selectedIds->map(fn($id)=>(string)$id)->values()));

    try{
        const saved=JSON.parse(sessionStorage.getItem(key)||'[]');
        if(Array.isArray(saved)) saved.forEach(id=>selected.add(String(id)));
    }catch(e){}

    const sync=()=>{
        boxes.forEach(box=>{
            const checked=selected.has(String(box.value));
            box.checked=checked;
            box.closest('.hero-product-card')?.classList.toggle('is-selected',checked);
        });
        if(hidden){
            hidden.replaceChildren(...[...selected].slice(0,90).map(id=>{
                const input=document.createElement('input');
                input.type='hidden';
                input.name='product_ids[]';
                input.value=id;
                return input;
            }));
        }
        try{sessionStorage.setItem(key,JSON.stringify([...selected].slice(0,90)))}catch(e){}
    };

    boxes.forEach(box=>box.addEventListener('change',function(){
        const id=String(this.value);
        if(this.checked){
            if(selected.size>=90){this.checked=false;return}
            selected.add(id);
        }else{
            selected.delete(id);
        }
        sync();
    }));

    form?.addEventListener('submit',function(){
        try{sessionStorage.removeItem(key)}catch(e){}
        boxes.forEach(box=>box.removeAttribute('name'));
    });

    sync();
});
</script>
@endpush
