{!! NoCaptcha::renderJs() !!}
{!! NoCaptcha::display() !!}
@if ($errors->has('g-recaptcha-response'))
    <span class="text-danger">{{ $errors->first('g-recaptcha-response') }}</span>
@endif
