@component('mail::message')
# Beyond Deadline Cases Report

Hello, **{{ $recipientName }}**,

The following cases have exceeded their deadline as of **{{ $reportDate }}**. Please take the necessary action.

@component('mail::table')
| No. | Case No. | Establishment | Beyond Fields |
|-----|----------|---------------|---------------|
@foreach($cases as $case)
| {{ $loop->iteration }} | {{ $case['case_no'] }} | {{ $case['establishment'] }} | {{ $case['beyond_summary'] }} |
@endforeach
@endcomponent

---

**Field Reference:**
- **Docket** — Status (Docket) is Beyond
- **1st MC** — Status (1st MC) is Beyond
- **2nd MC** — Status (2nd MC) is Beyond
- **PO PCT** — Status (PO PCT) is Beyond
- **PCT (96 days)** — Status (PCT) is Beyond

@component('mail::button', ['url' => config('app.url') . '/case', 'color' => 'red'])
View Active Cases
@endcomponent

This is an automated notification sent daily at 7:00 AM.

Thanks,<br>
{{ config('app.name') }}
@endcomponent