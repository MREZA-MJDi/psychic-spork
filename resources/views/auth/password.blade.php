<div class="auth-field">
    <label for="{{ $name }}">{{ $label }}</label>
    <div class="auth-password-wrap">
        <input id="{{ $name }}" name="{{ $name }}" type="password" autocomplete="{{ $autocomplete }}"
            @if($autocomplete === 'new-password') minlength="8" @endif required data-password-input>
        <button class="auth-password-toggle" type="button" data-password-toggle aria-label="نمایش رمز عبور" aria-pressed="false">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2.5 12s3.5-5 9.5-5 9.5 5 9.5 5-3.5 5-9.5 5-9.5-5-9.5-5Z"/><circle cx="12" cy="12" r="2.5"/></svg>
        </button>
    </div>
</div>
