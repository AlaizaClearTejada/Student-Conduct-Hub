<x-mail::message>
# Welcome to the Student Conduct Management System

Hello {{ $user->first_name }},

An account has been automatically created for you in the Student Conduct Management System. You can use this account to track any official notices or records.

Your login credentials are as follows:

**Email:** {{ $user->email }}  
**Password:** {{ $password }}

<x-mail::panel>
For security reasons, we strongly recommend logging in and changing your password immediately.
</x-mail::panel>

<x-mail::button :url="config('app.url') . '/login'">
Log In to Your Account
</x-mail::button>

If you have any questions or concerns, please contact the disciplinary office.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
