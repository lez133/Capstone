<p>Dear {{ $document->beneficiary->first_name }},</p>
<p>Your document ({{ $document->document_type }}) has been <strong>rejected</strong>.</p>
<p>Reason: {{ $reason }}</p>
<p>Please contact us for more information.</p>
