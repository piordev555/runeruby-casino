<h1>Hello!</h1>

<p>You are receiving this email because we received a password reset request for your account.</p>

<p>Visit this URL for a password reset: <a href="{{ env('APP_URL') }}/password/reset/{{ $user }}/{{ $token }}">{{ env('APP_URL') }}/password/reset/{{ $user }}/{{ $token }}</a></p>

<p>If you did not request a password reset, no further action is required.</p>

<p>Regards,<br>{{ env('APP_URL') }}</p>
