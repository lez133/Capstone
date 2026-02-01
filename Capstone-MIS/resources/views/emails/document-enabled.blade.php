<p>Dear {{ $document->beneficiary->first_name }},</p>
<p>Your document ({{ $document->document_type }}) has been <strong>enabled</strong>.</p>
@if($reason)
<p>Reason: {{ $reason }}</p>
@endif
<p>You may now use your document as needed.</p>
