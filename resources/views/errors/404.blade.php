@if(env('APP_DEBUG', false)) {{ url()->current() }} @endif
@include('errors.error', ['code' => 404])
