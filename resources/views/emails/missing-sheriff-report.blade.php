@component('mail::message')
# Missing Sheriff Reports

Hello, **{{ $recipientName }}**,

The following cases currently assigned to you are missing a sheriff report for **{{ $monthLabel }}**.

---

@component('mail::table')
| No. | Case No. | Establishment |
|-----|----------|---------------|
@foreach($missingCases as $case)
| {{ $loop->iteration }} | {{ $case['case_no'] }} | {{ $case['establishment'] }} |
@endforeach
@endcomponent

---

Please log in and submit the missing report(s) as soon as possible.

@component('mail::button', ['url' => config('app.url') . '/case', 'color' => 'primary'])
Go to My Cases
@endcomponent

This is an automated notification sent on the first weekday of the month.

Thanks,<br>
{{ config('app.name') }}
@endcomponent