<x-mail::message>
{{ __('auth.forgot.description') }}

<x-mail::button :url="config('app.frontend_url') . '/auth.html#/reset-password?token=' . $token . '&email=' . $user->email">
{{ __('auth.forgot.reset') }}
</x-mail::button>

*{{ __('sincerely')}}*,<br>
{{ config('app.name') }}.

</x-mail::message>
