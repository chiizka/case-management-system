@component('mail::message')
# Welcome to Our System!

Hello {{ $userName }},

Your password has been set successfully! You can now access your account using your email address and the password you just created.

@component('mail::button', ['url' => $loginUrl])
Login to Your Account
@endcomponent

If you have any questions or need assistance, please don't hesitate to contact our support team.

Thanks,<br>
{{ config('app.name') }}
@endcomponent