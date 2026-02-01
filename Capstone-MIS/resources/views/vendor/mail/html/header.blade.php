@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'MSWD')
<img src="{{ asset('img/mswd-card.jpg') }}" alt="Logo" style="height: 60px; margin-bottom: 16px;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
