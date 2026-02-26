    <?php

    use App\Http\Controllers\CasesController;
    use App\Http\Controllers\FrontController;
    use App\Http\Controllers\Admin\UserController;
    use App\Http\Controllers\Auth\LoginController;
    use App\Http\Controllers\ArchivedController;
    use App\Http\Controllers\InspectionsController;
    use App\Http\Controllers\DocketingController;
    use App\Http\Controllers\HearingProcessController;
    use App\Http\Controllers\ReviewAndDraftingController;
    use App\Http\Controllers\OrderAndDispositionController;
    use App\Http\Controllers\ComplianceAndAwardController;
    use App\Http\Controllers\AppealsAndResolutionController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\LogController;
    use App\Http\Controllers\DocumentTrackingController;


    Route::get('/', [FrontController::class, 'index'])->name('home');

    Route::get('/login', [FrontController::class, 'login'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    // 2FA Routes
    Route::get('/2fa/verify', [LoginController::class, 'show2FAForm'])->name('2fa.verify');
    Route::post('/2fa/verify', [LoginController::class, 'verify2FA'])->name('2fa.verify.post');
    Route::post('/2fa/resend', [LoginController::class, 'resend2FA'])->name('2fa.resend');

    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Password reset routes
    Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

    Route::middleware('auth')->group(function () {

        // Profile Routes (must be authenticated)
        Route::middleware(['auth'])->group(function () {
            Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'index'])->name('profile.index');
            Route::put('/profile/update', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');
        });
        
        // Dashboard/Home
        Route::get('/', [FrontController::class, 'index'])->name('home');
        
        // User Management - Admin Only
        Route::middleware('role:admin')->group(function () {
            Route::get('/users', [FrontController::class, 'users'])->name('users');
            Route::post('/user', [UserController::class, 'store'])->name('user.post');
            Route::post('/user/{id}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
            Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
            Route::put('/user/{id}', [UserController::class, 'update'])->name('user.update');
            Route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
        });
        
        // Cases - Admin, All Province Roles, MALSU, Case Management, Records
        Route::middleware('role:admin,province_albay,province_camarines_sur,province_camarines_norte,province_catanduanes,province_masbate,province_sorsogon,malsu,case_management,records')->group(function () {
            
            // ✅ SPECIFIC ROUTES MUST COME FIRST (before Route::resource)
            Route::post('/case/import-csv', [CasesController::class, 'importCsv'])->name('case.import-csv');
            
            // ✅ ADD THIS: Archive route (called by JavaScript)
            Route::post('/case/{id}/archive', [CasesController::class, 'moveToNextStage'])->name('case.archive');
            
            // ✅ KEEP THIS: Next stage route (for normal progression)
            Route::post('/case/{id}/next-stage', [CasesController::class, 'moveToNextStage'])->name('case.nextStage');
            
            Route::put('/case/{id}/inline-update', [CasesController::class, 'inlineUpdate'])->name('case.inlineUpdate');
            Route::get('/case/load-tab/{tabNumber}', [CasesController::class, 'loadTabData'])->name('case.loadTab');
            Route::get('/case/{id}/document-history', [CasesController::class, 'getDocumentHistory'])->name('case.documentHistory');
            Route::get('/case/{id}/documents', [CasesController::class, 'getDocuments'])->name('case.documents');
            Route::post('/case/{id}/documents', [CasesController::class, 'saveDocuments'])->name('case.documents.save');
            
            // Document file upload routes
            Route::post('/case/{caseId}/documents/{documentId}/upload', [CasesController::class, 'uploadDocumentFile'])->name('case.documents.upload');
            Route::get('/case/{caseId}/documents/{documentId}/download', [CasesController::class, 'downloadDocumentFile'])->name('case.documents.download');
            Route::delete('/case/{caseId}/documents/{documentId}/file', [CasesController::class, 'deleteDocumentFile'])->name('case.documents.deleteFile');
            
            // ✅ RESOURCE ROUTE COMES LAST
            Route::resource('case', CasesController::class);
            
            Route::get('/archive', [ArchivedController::class, 'index'])->name('archive.index');

            Route::post('/archive/{caseId}/appeal', [ArchivedController::class, 'storeAppeal'])
            ->name('archive.appeal')
            ->middleware('role:admin,malsu,case_management');

            Route::get('/analytics', [App\Http\Controllers\AnalyticsController::class, 'index'])->name('analytics.index');

            Route::post('/reports/form1', [App\Http\Controllers\ReportController::class, 'generateForm1'])
            ->name('reports.form1.generate');

            Route::post('/reports/form3', [App\Http\Controllers\ReportController::class, 'generateForm3'])
            ->name('reports.form3.generate');
        });

        // Remove this duplicate line at the end (line 134):
        // Route::post('/case/import-csv', [App\Http\Controllers\CasesController::class, 'importCsv'])->name('case.import-csv');


        // Inspections - Admin, MALSU, Case Management, All Province Roles
        Route::middleware('role:admin,malsu,case_management,province_albay,province_camarines_sur,province_camarines_norte,province_catanduanes,province_masbate,province_sorsogon')->group(function () {
            Route::resource('inspection', InspectionsController::class);
            Route::put('/inspection/{inspection}/inline-update', [InspectionsController::class, 'inlineUpdate'])->name('inspection.inlineUpdate');
        });

        // Docketing - Admin, Case Management, All Province Roles
        Route::middleware('role:admin,case_management,province_albay,province_camarines_sur,province_camarines_norte,province_catanduanes,province_masbate,province_sorsogon')->group(function () {
            Route::resource('docketing', DocketingController::class);
            Route::put('/docketing/{id}/inline-update', [DocketingController::class, 'inlineUpdate'])->name('docketing.inlineUpdate');
        });

        // Hearing Process - Admin, Case Management, All Province Roles
        Route::middleware('role:admin,case_management,province_albay,province_camarines_sur,province_camarines_norte,province_catanduanes,province_masbate,province_sorsogon')->group(function () {
            Route::resource('hearing', HearingProcessController::class);
            Route::post('hearing-process', [HearingProcessController::class, 'store'])->name('hearing-process.store');
            Route::get('hearing-process/{id}', [HearingProcessController::class, 'show'])->name('hearing-process.show');
            Route::get('hearing-process/{id}/edit', [HearingProcessController::class, 'edit'])->name('hearing-process.edit');
            Route::put('hearing-process/{id}', [HearingProcessController::class, 'update'])->name('hearing-process.update');
            Route::delete('hearing-process/{id}', [HearingProcessController::class, 'destroy'])->name('hearing-process.destroy');
            Route::put('/hearing/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing.inlineUpdate');
            Route::get('/hearing/{id}/get', [HearingProcessController::class, 'getHearingProcess'])->name('hearing.get');
            Route::put('/hearing-process/{id}/inline-update', [HearingProcessController::class, 'inlineUpdate'])->name('hearing-process.inlineUpdate');
        });

        // Review and Drafting - Admin, Case Management
        Route::middleware('role:admin,case_management')->group(function () {
            Route::resource('review-and-drafting', ReviewAndDraftingController::class);
            Route::put('/review-and-drafting/{id}/inline-update', [ReviewAndDraftingController::class, 'inlineUpdate'])->name('review-and-drafting.inline-update');
        });

        // Orders and Disposition - Admin, Case Management
        Route::middleware('role:admin,case_management')->group(function () {
            Route::resource('orders-and-disposition', OrderAndDispositionController::class);
            Route::put('/orders-and-disposition/{id}/inline-update', [OrderAndDispositionController::class, 'inlineUpdate'])->name('orders-and-disposition.inline-update');
        });

        // Compliance and Awards - Admin, Case Management
        Route::middleware('role:admin,case_management')->group(function () {
            Route::resource('compliance-and-awards', ComplianceAndAwardController::class);
            Route::put('/compliance-and-awards/{id}/inline-update', [ComplianceAndAwardController::class, 'inlineUpdate'])->name('compliance-and-awards.inline-update');
        });

        // Appeals and Resolution - Admin, Case Management, Malsu
        Route::middleware('role:admin,case_management,malsu')->group(function () {
            Route::resource('appeals-and-resolution', AppealsAndResolutionController::class);
            Route::put('/appeals-and-resolution/{id}/inline-update', [AppealsAndResolutionController::class, 'inlineUpdate'])->name('appeals-and-resolution.inline-update');
        });

        // Document Tracking - All authenticated users
        Route::middleware(['auth'])->group(function() {
            Route::get('/documents/tracking', [DocumentTrackingController::class, 'index'])->name('documents.tracking');
            Route::post('/documents/transfer', [DocumentTrackingController::class, 'transfer'])->name('documents.transfer');
            Route::post('/documents/{id}/receive', [DocumentTrackingController::class, 'receive'])->name('documents.receive');
            Route::get('/documents/{id}/history', [DocumentTrackingController::class, 'history'])->name('documents.history');
        });

        Route::post('/case/import-csv', [App\Http\Controllers\CasesController::class, 'importCsv'])->name('case.import-csv');
    });
