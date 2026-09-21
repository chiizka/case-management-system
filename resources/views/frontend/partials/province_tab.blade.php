<div class="alert alert-success alert-dismissible fade" role="alert"
     id="success-alert-prov-{{ $province }}" style="display: none;">
    <span id="success-message-prov-{{ $province }}"></span>
    <button type="button" class="close"
            onclick="hideAlert('success-alert-prov-{{ $province }}')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<div class="alert alert-danger alert-dismissible fade" role="alert"
     id="error-alert-prov-{{ $province }}" style="display: none;">
    <span id="error-message-prov-{{ $province }}"></span>
    <button type="button" class="close"
            onclick="hideAlert('error-alert-prov-{{ $province }}')">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

<!-- Search Row -->
<div class="d-flex justify-content-between align-items-center mb-3 custom-search-container">
    <div class="d-flex align-items-center">
        <label class="mr-2 mb-0" style="font-size: 0.8rem;">Search:</label>
        <input type="search"
               class="form-control form-control-sm"
               id="customSearchProv-{{ $province }}"
               placeholder="Search {{ $provinceLabel }} cases..."
               style="width: 220px;">
    </div>
    <div>
        <span class="badge badge-info" style="font-size: 0.85rem;">
            {{ $cases->count() }} active case(s)
        </span>
    </div>
</div>

<!-- Table -->
<div class="table-container">
    <table class="table table-bordered compact-table sticky-table"
           id="dataTableProv-{{ $province }}"
           style="min-width: 100%;">
        <thead>
            <tr>
                <th>Actions</th>
                <th>No.</th>
                <th>Inspection ID</th>
                <th>Case No.</th>
                <th>Establishment Name</th>
                <th>PO</th>
                <th>Type of Industry</th>

                <th>Date of Inspection</th>
                <th>Mode</th>
                <th>Name of Inspector</th>
                <th>Authority No.</th>
                <th>Date of NR</th>
                <th>Lapse 20 Day Correction Period</th>
                <th>PCT for Docketing</th>
                <th>Date Scheduled/Docketed</th>
                <th>Aging (Docket)</th>
                <th>Status (Docket)</th>
                <th>Hearing Officer (MIS)</th>
                <th>Date of 1st MC (Actual)</th>
                <th>1st MC PCT</th>
                <th>Status (1st MC)</th>
                <th>Date of 2nd/Last MC (Actual)</th>
                <th>2nd/Last MC PCT</th>
                <th>Status (2nd MC)</th>
                <th>Case Folder Forwarded to RO</th>
                <th>Draft Order from PO (Type)</th>
                <th>Applicable Draft Order? (Y/N)</th>
                <th>Complete Case Folder? (Y/N)</th>
                <th>PO PCT</th>
                <th>Aging (PO PCT)</th>
                <th>Status (PO PCT)</th>
                <th>TWG</th>
                <th>Date Received from PO</th>
                <th>MIS Status (Forwarded to RD's Account)</th>
                <th>Reviewer/Drafter</th>
                <th>Date Received by Reviewer/Drafter</th>
                <th>Date Returned from Drafter (Initial Review)</th>
                <th>Aging (10 Days TSSD)</th>
                <th>Status (Reviewer/Drafter)</th>
                <th>Draft Order of TSSD Reviewer/Drafter</th>
                <th>Final Review<br><small class="text-muted font-weight-normal">Engr. R.L. Aranas</small></th>
                <th>Final Review<br><small class="text-muted font-weight-normal">Chief Ching B. Banania </small></th>
                <th>Final Review<br><small class="text-muted font-weight-normal">V. Capayas </small></th>
                <th>Final Review<br><small class="text-muted font-weight-normal"> Atty. N. Leaño II/ Atty. A.</small></th>
                <th>Date Received by Drafter for Finalization</th>
                <th>Date Returned to Case Mngt for Signature</th>
                <th>Aging (2 Days)</th>
                <th>Status (Finalization)</th>
                <th>PCT (96 days from NR)</th>
                <th>Date Signed (MIS)</th>
                <th>Status (PCT)</th>
                <th>Reference Date (PCT)</th>
                <th>Aging (PCT)</th>
                <th>Disposition (MIS)</th>
                <th>Disposition (Actual)</th>
                <th>Findings to be Complied in the Order</th>
                <th>Compliance Order Monetary Award</th>
                <th>OSH Penalty</th>
                <th>Affected Male</th>
                <th>Affected Female</th>
                <th>Date of Order (Actual)</th>
                <th>Released Date (Actual)</th>
                <th>1st Order Dismissal - CNPC</th>
                <th>TAVable? (&lt;10 Workers)</th>
                <th>Scanned Order (1st Order)</th>
                <th>With Deposited Monetary Claims?</th>
                <th>Amount Deposited</th>
                <th>With Order of Payment/Notice?</th>
                <th>Status (Claims Received)</th>
                <th>Note</th>
                <th>Date Received by Respondent/Employer</th>
                <th>Date Received by Affected Employee/s</th>
                <th>Status of Case after 1st Order</th>
                <th>Date of Notice of Finality (Dismissed)</th>
                <th>Released Date of Notice of Finality</th>
                <th>Scanned Notice of Finality</th>
                <th>Updated/Ticked in MIS?</th>
                <th>Date Evaluated</th>
                <th>Name of Evaluator</th>
                <th>2nd Order Drafter</th>
                <th>Date Received by Drafter (C&amp;T/CNPC)</th>
                <th>Date Returned to Case Mngt (C&amp;T/CNPC)</th>
                <th>Review (C&amp;T/CNPC)<br><small class="text-muted font-weight-normal">Engr. R. Aranas</small></th>
                <th>Review (C&amp;T/CNPC)<br><small class="text-muted font-weight-normal">TSSD Chief</small></th>
                <th>Review (C&amp;T/CNPC)<br><small class="text-muted font-weight-normal">Med-Arb</small></th>
                <th>Review (C&amp;T/CNPC)<br><small class="text-muted font-weight-normal">ARD</small></th>
                <th>Date Received by Drafter for Finalization (2nd Order)</th>
                <th>Date Returned to Case Mngt for Signature (2nd Order)</th>
                <th>Date of Order (2nd Order/CNPC)</th>
                <th>Released Date (2nd Order/CNPC)</th>
                <th>Scanned Order (2nd Order/CNPC)</th>
                <th>Date Received by MALSU (Execution/MR/Appeal)</th>
                <th>Scanned Indorsement to MALSU</th>
                <th>Motion for Reconsideration</th>
                <th>Date Received by MALSU (Finality/MR/Appeal)</th>
                <th>Date of Resolution (MR)</th>
                <th>Released Date of Resolution (MR)</th>
                <th>Scanned Resolution (MR)</th>
                <th>Date of Appeal</th>
                <th>Date Indorsed to Office of Secretary</th>
                <th>Logbook Page Number</th>
                <th>Date Indorsed to Records (For Archive)</th>
                <th>Scanned Copy of Indorsement</th>
                <th>Remarks/Notes</th>

                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @include('frontend.partials.tab0-rows', ['cases' => $cases, 'showLocationBadge' => true])
        </tbody>
    </table>
</div>