@component('mail::message')
# Account Disabled

Dear {{ $beneficiary->first_name }} {{ $beneficiary->last_name }},

Your account has been disabled by the administrator. If you believe this is a mistake, please contact our office.

Regards,<br>
{{ config('app.name') }}
@endcomponent
