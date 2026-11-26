@props(['href', 'icon', 'label'])

<a href="{{ $href }}" aria-label="{{ $label }}" title="{{ $label }}" {{ $attributes->class('store-action-icon') }}>
    <x-store.icon :name="$icon" />
</a>
