@props(['filters', 'exclude'])

@foreach(Arr::except($filters, $exclude) as $name => $value)
    @if(filled($value))
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endif
@endforeach
