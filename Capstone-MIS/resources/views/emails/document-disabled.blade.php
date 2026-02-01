<p>Dear {{ $document->beneficiary->first_name }},</p>
<p>Your document ({{ $document->document_type }}) has been <strong>disabled</strong>.</p>
@if($reason)
<p>Reason: {{ $reason }}</p>
@endif
<p>Please contact us for more information.</p>
