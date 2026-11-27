@php
    $tone = match($status) {
        'submitted', 'under_review', 'awaiting_original', 'original_received' => 'warning',
        'cleared' => 'success',
        'accepted', 'deposited' => 'info',
        'rejected', 'bounced' => 'danger',
        default => 'neutral',
    };
@endphp
<span class="cheque-status cheque-status--{{ $tone }}">{{ $shortStatuses[$status] ?? $status }}</span>
