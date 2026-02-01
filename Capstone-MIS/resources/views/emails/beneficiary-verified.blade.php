@component('mail::message')
# Account Verified

Dear {{ $beneficiary->first_name }} {{ $beneficiary->last_name }},

Your account has been successfully verified. You can now access all the features of our system.

@if(strtolower($beneficiary->beneficiary_type) === 'senior citizen')
OSCA Number: {{ $beneficiary->osca_number ?? 'N/A' }}
@elseif(strtolower($beneficiary->beneficiary_type) === 'pwd')
PWD ID Number: {{ $beneficiary->pwd_id_number ?? 'N/A' }}
@endif

Thank you for your patience.

Regards,<br>
{{ config('app.name') }}
@endcomponent
