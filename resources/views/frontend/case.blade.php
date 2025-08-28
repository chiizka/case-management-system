@extends('frontend.layouts.app')
@section('content')

<!-- Main Content -->
<div id="content">

    <!-- Begin Page Content -->
    <div class="container-fluid">
        
        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="dataTableTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab1-tab" data-toggle="tab" href="#tab1" role="tab" aria-controls="tab1" aria-selected="true">
                    Active Cases
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab2-tab" data-toggle="tab" href="#tab2" role="tab" aria-controls="tab2" aria-selected="false">
                    Provincial Cases
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab3-tab" data-toggle="tab" href="#tab3" role="tab" aria-controls="tab3" aria-selected="false">
                    Regional Cases
                </a>
            </li>
        </ul>

        <!-- Tabs Content -->
        <div class="tab-content mt-3" id="dataTableTabsContent">

            <!-- Tab 1 -->
            <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
    <div class="card shadow mb-4">
        <div class="card-body">

            <!-- Search + Buttons Row -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <!-- Search on the left -->
                <div class="dataTables_filter" id="dataTable1_filter"></div>

                <!-- Buttons on the right -->
                <div>
                    <button class="btn btn-secondary btn-sm" id="toggleEdit">Edit</button>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addCaseModal">
                        + Add Case
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-sm custom-table" id="dataTable1" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Case No.</th>
                            <th>Type of Case</th>
                            <th>Complainant Information</th>
                            <th>Respondent Information</th>
                            <th>Case Details</th>
                            <th>Date filed</th>
                            <!-- Hidden Action column -->
                            <th class="action-col d-none">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>01</td>
                            <td>System Architect</td>
                            <td>Edinburgh</td>
                            <td>61</td>
                            <td>2011/04/25</td>
                            <td>$320,800</td>
                            <td class="action-col d-none">
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                        <tr>
                            <td>03</td>
                            <td>Accountant</td>
                            <td>Tokyo</td>
                            <td>63</td>
                            <td>2011/07/25</td>
                            <td>$170,750</td>
                            <td class="action-col d-none">
                                <button class="btn btn-sm btn-warning">Edit</button>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

            <!-- Tab 2 -->
            <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm custom-table" id="dataTable2" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Information</th>
                                        <th>Respondent Information</th>
                                        <th>Case Details</th>
                                        <th>Date filed</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>17</td>
                                        <td>Example B1</td>
                                        <td>Example C1</td>
                                        <td>Example C1</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                    </tr>
                                    <tr>
                                        <td>22</td>
                                        <td>Example B2</td>
                                        <td>Example C2</td>
                                        <td>Example C1</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 3 -->
            <div class="tab-pane fade" id="tab3" role="tabpanel" aria-labelledby="tab3-tab">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm custom-table" id="dataTable3" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>Case No.</th>
                                        <th>Type of Case</th>
                                        <th>Complainant Information</th>
                                        <th>Respondent Information</th>
                                        <th>Case Details</th>
                                        <th>Date filed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>33</td>
                                        <td>Data 2</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                    </tr>
                                    <tr>
                                        <td>70</td>
                                        <td>Data 5</td>
                                        <td>Data 6</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                        <td>Data 3</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- End Tabs Content -->

    </div>
    <!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Modal -->
<div class="modal fade" id="addCaseModal" tabindex="-1" role="dialog" aria-labelledby="addCaseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addCaseModalLabel">Add New Case</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Form -->
        <form>
            <div class="form-group">
                <label for="caseName">Case Name</label>
                <input type="text" class="form-control" id="caseName" placeholder="Enter case name">
            </div>
            <div class="form-group">
                <label for="caseDetails">Details</label>
                <textarea class="form-control" id="caseDetails" rows="3" placeholder="Enter case details"></textarea>
            </div>
            <div class="form-group">
                <label for="caseDate">Date</label>
                <input type="date" class="form-control" id="caseDate">
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save Case</button>
      </div>
    </div>
  </div>
</div>

@stop
