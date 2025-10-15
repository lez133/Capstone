
@component('mail::message')
# Account Verified

Dear {{ $beneficiary->first_name }} {{ $beneficiary->last_name }},

Your account has been successfully verified. You can now access all the features of our system.

OSCA Number: {{ ($beneficiary->osca_number) }}

Thank you for your patience.

Regards,<br>
{{ config('app.name') }}
@endcomponent
