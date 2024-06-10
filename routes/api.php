<?php


use App\Http\Controllers\CacheController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DbVersionController;
use App\Http\Controllers\FcmTokenController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\MovieRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecMenuItemController;
use App\Http\Controllers\SecRolePermissionController;
use App\Http\Controllers\SecUserAccessTblController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebSeriesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\FileUploadController;







Route::post('/register', [RegistrationController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/upload-image', [FileUploadController::class, 'uploadImage']);
Route::post('/upload-video', [FileUploadController::class, 'uploadVideo']);
Route::post('/upload-image-gcp', [FileUploadController::class, 'uploadGcpImage']);
Route::post('/upload-video-gcp', [FileUploadController::class, 'uploadGcpVideo']);
Route::post('/getSignedUrl', [FileUploadController::class, 'getSignedUrl']);
Route::get('/test-otp', [CommonController::class, 'getIosOtpLog']);
Route::get('/flushSession', [AuthController::class, 'flushSession']);



// Dashboard
Route::prefix('dashboard')->group(function () {
    Route::get('/total-users', [DashboardController::class, 'getTotalUsers']);
    Route::get('/vod-views', [DashboardController::class, 'getTotalVodViews']);
    Route::get('/live-views', [DashboardController::class, 'getTotalLiveViews']);
    Route::get('/today-login-users', [DashboardController::class, 'getTodayLoginUsers']);
    Route::get('/top-ten-content', [DashboardController::class, 'getTopTenContent']);
    Route::get('/top-live-content', [DashboardController::class, 'getTopLiveContent']);
    Route::get('/monthly-active-users', [DashboardController::class, 'getMonthlyActiveUsers']);
    Route::get('/daily-active-users', [DashboardController::class, 'getDailyActiveUsers']);
    Route::get('/highest-con-users', [DashboardController::class, 'getHeightConcurrencyUsers']);
    Route::get('/hourly-graph-report', [DashboardController::class, 'getHourlyGraphReport']);
    Route::get('/live-audience', [DashboardController::class, 'getLiveAudience']);
    Route::get('/top-vod-content', [DashboardController::class, 'getTopVodContent']);

});

Route::middleware(['idle.time','custom.auth'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
//    Route::post('/getSignedUrl', [FileUploadController::class, 'getSignedUrl']);



    Route::resource('sec-menu-items', SecMenuItemController::class);
    Route::get('sec-menu-items/get/all', [SecMenuItemController::class, 'getAllMenu']);
    Route::get('sec-menu-items/get/parentMenu', [SecMenuItemController::class, 'getParentMenuDropdown']);
    Route::post('setPermissions', [SecRolePermissionController::class, 'setPermissions']);
    Route::put('updatePermissions', [SecRolePermissionController::class, 'updatePermissions']);



//Route::resource('sec-user-access-tbl', SecUserAccessTblController::class);
    Route::put('sec-user-access-tbl/{id}', [SecUserAccessTblController::class, 'update']);

    Route::resource('sec-role-permissions', SecRolePermissionController::class);

    // Contents
    Route::prefix('contents')->group(function () {
        Route::get('/getLiveContentDropdown', [ContentController::class, 'getLiveContentDropdown']);
        Route::get('/getContentHomeData', [ContentController::class, 'getContentHomeData']);
        Route::get('/getLiveContents', [ContentController::class, 'getLiveContents']);
        Route::get('/getVodContents', [ContentController::class, 'getVodContents']);
        Route::get('/getFeaturedContents', [ContentController::class, 'getFeaturedContents']);
        Route::post('/store', [ContentController::class, 'store']);
        Route::patch('/update', [ContentController::class, 'update']);
        Route::delete('/delete', [ContentController::class, 'destroy']);
        Route::get('/show', [ContentController::class, 'show']);
        Route::get('/download-list', [ContentController::class, 'getDownloadList']);
        Route::get('/download-vod', [ContentController::class, 'downloadVod']);

    });

    // Categories
    Route::prefix('categories')->group(function () {
        Route::get('/get', [CategoryController::class, 'getCategoriesList']);
        Route::get('/get-all', [CategoryController::class, 'getCategoriesDropdown']);

        Route::get('/show', [CategoryController::class, 'show']);
        Route::post('/store', [CategoryController::class, 'store']);
        Route::patch('/update', [CategoryController::class, 'update']);

    });

    // SubCategories
    Route::prefix('subcategories')->group(function () {
        Route::get('/get', [SubCategoryController::class, 'getSubCategoriesList']);
        Route::get('/show', [SubCategoryController::class, 'show']);
        Route::post('/store', [SubCategoryController::class, 'store']);
        Route::patch('/update', [SubCategoryController::class, 'update']);

    });

    // FCM
    Route::prefix('fcm')->group(function () {
        Route::get('/getNotification', [FcmTokenController::class, 'getNotification']);
        Route::post('/send', [FcmTokenController::class, 'sendFCMNotification']);

    });

    // Users
    Route::prefix('users')->group(function () {
        Route::get('/get', [UserController::class, 'getUsersList']);
        Route::get('/show', [UserController::class, 'show']);
        Route::post('/store', [UserController::class, 'store']);
        Route::patch('/update', [UserController::class, 'update']);
        Route::get('/get-all', [UserController::class, 'getUsersListDropdown']);

        //ACL
        Route::get('/permissions/{id}', [UserController::class, 'getUserRoleAndPermissions']);



    });

    // Roles
    Route::prefix('roles')->group(function () {
        Route::get('/get-all', [RoleController::class, 'getRolesListAll']);
        Route::get('/get', [RoleController::class, 'getRolesList']);
        Route::get('/show', [RoleController::class, 'show']);
        Route::post('/store', [RoleController::class, 'store']);
        Route::patch('/update', [RoleController::class, 'update']);

    });

    // DB Version
    Route::prefix('version')->group(function () {
        Route::get('/get', [DbVersionController::class, 'getDbVersionList']);
        Route::get('/show', [DbVersionController::class, 'show']);
        Route::patch('/update', [DbVersionController::class, 'update']);

    });

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/content-views', [ReportController::class, 'contentViewsDateWise']);
        Route::get('/login-log', [ReportController::class, 'getLoginLogViewReport']);
        Route::get('/con-current-user', [ReportController::class, 'getconCurrentUserReport']);
        Route::get('/unique-user-monthly', [ReportController::class, 'getUniqueUserMonthlyReport']);
        Route::get('/unique-user-daily', [ReportController::class, 'getUniqueUserDailyReport']);
        Route::get('/live-audience-daily', [ReportController::class, 'getLiveAudienceReport']);
        Route::get('/payment-report', [ReportController::class, 'getPaymentData']);
        Route::get('/audit-report', [ReportController::class, 'auditReport']);
        Route::get('/audit-report/show', [ReportController::class, 'auditReportShowByID']);


    });

    // Web Series
    Route::prefix('web-series')->group(function () {
        Route::get('/get', [WebSeriesController::class, 'getWebSeriesList']);
        Route::get('/get-all', [WebSeriesController::class, 'getWebSeriesDropdown']);

        Route::get('/show', [WebSeriesController::class, 'show']);
        Route::post('/store', [WebSeriesController::class, 'store']);
        Route::patch('/update', [WebSeriesController::class, 'update']);

    });


    // Interest List
    Route::prefix('interest')->group(function () {
        Route::get('/get', [InterestController::class, 'getInterestList']);
        Route::get('/get-all', [InterestController::class, 'getInterestDropdown']);

        Route::get('/show', [InterestController::class, 'show']);
        Route::post('/store', [InterestController::class, 'store']);
        Route::patch('/update', [InterestController::class, 'update']);

    });

    // Contact List
    Route::prefix('contacts')->group(function () {
        Route::get('/get', [ContactController::class, 'getContactList']);

    });

    // Movie Request
    Route::prefix('movies')->group(function () {
        Route::get('/request/get', [MovieRequestController::class, 'getMovieRequestList']);

    });

    Route::get('/fetch-data/{key}', [CacheController::class, 'fetchData']);
});
