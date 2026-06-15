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
                <th>Mode</th>
                <th>PO</th>
                <th>Type of Industry</th>
                <th>Date of Inspection</th>
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
                <th>PO PCT</th>
                <th>Aging (PO PCT)</th>
                <th>Status (PO PCT)</th>
                <th>PCT (96 days from NR)</th>
                <th>Status (PCT)</th>
                <th>Date Signed (MIS)</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @include('frontend.partials.tab0-rows', ['cases' => $cases])
        </tbody>
    </table>
</div>