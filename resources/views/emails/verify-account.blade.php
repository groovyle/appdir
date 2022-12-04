@component('mail::message')
## Hello {{ $user->name }},

Thank you for registering an account at {{ app_name() }}. Your new account's status is inactive. To activate your account, please verify this email address using the following link:

@component('mail::button', ['url' => $verification_url])
Verify Email Address
@endcomponent

After your account is activated, you will be able to fully use all the available features on our website.
If you did not register an account, just ignore this email.

Thanks,<br>
{{ app_name() }}
@endcomponent
