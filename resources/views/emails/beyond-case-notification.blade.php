@component('mail::message')
# Case Deadline Report

Hello, **{{ $recipientName }}**,

Here is your daily case deadline report as of **{{ $reportDate }}**.

---

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SECTION 1: BEYOND DEADLINE                             --}}
{{-- ═══════════════════════════════════════════════════════ --}}
@if(count($beyondCases) > 0)
## 🔴 Beyond Deadline ({{ count($beyondCases) }} case/s)

These cases have already exceeded their deadline. Immediate action is required.

@component('mail::table')
| No. | Case No. | Establishment | PO Office | Beyond Fields |
|-----|----------|---------------|-----------|---------------|
@foreach($beyondCases as $case)
| {{ $loop->iteration }} | {{ $case['case_no'] }} | {{ $case['establishment'] }} | {{ $case['po_office'] }} | {{ $case['beyond_summary'] }} |
@endforeach
@endcomponent

@else
## 🔴 Beyond Deadline

✅ No cases are currently beyond deadline.

@endif

---

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- SECTION 2: UPCOMING DEADLINES (within 5 days)          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
@if(count($upcomingCases) > 0)
## 🟡 Upcoming Deadlines — Within 5 Days ({{ count($upcomingCases) }} case/s)

These cases are approaching their deadline. Please take action before they go Beyond.

@component('mail::table')
| No. | Case No. | Establishment | PO Office | Upcoming Deadline |
|-----|----------|---------------|-----------|-------------------|
@foreach($upcomingCases as $case)
| {{ $loop->iteration }} | {{ $case['case_no'] }} | {{ $case['establishment'] }} | {{ $case['po_office'] }} | {{ $case['upcoming_summary'] }} |
@endforeach
@endcomponent

@else
## 🟡 Upcoming Deadlines — Within 5 Days

✅ No cases have deadlines within the next 5 days.

@endif

---

**Deadline Reference:**
- **Docket** — PCT for Docketing (Lapse + 5 days)
- **PO PCT** — PO PCT deadline (Lapse + 45 days)
- **PCT (96 days)** — PCT 96 days from Date of NR

@component('mail::button', ['url' => config('app.url') . '/case', 'color' => 'red'])
View Active Cases
@endcomponent

This is an automated notification sent every weekday at 7:00 AM.

Thanks,<br>
{{ config('app.name') }}
@endcomponent