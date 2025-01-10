@component('mail::message')
# Introduction

The body of your message.

@component('mail::button', ['url' => 'http://coreapis.worksdemo.co.in/public/'])
Button Text
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
