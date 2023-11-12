<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\SmsController;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\FilesController;
use \App\Http\Controllers\StaffController;
use App\Http\Controllers\SupportController;
use \App\Http\Controllers\ProductController;
use \App\Http\Controllers\ReviewsController;
use \App\Http\Controllers\ServicesController;
use \App\Http\Controllers\AppointmentController;
use \App\Http\Controllers\NotificationsController;
use \App\Http\Controllers\MailController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//public routes
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

//protected routes
Route::middleware('auth:sanctum')->group( function () {
    Route::post('/logout',[AuthController::class, 'logout']);
  
    Route::get('/user-role', [AuthController::class, 'roles'])->name('roles');
    Route::post('/create-staff', [StaffController::class, 'createStaff']);
    Route::get('/get-staffs', [StaffController::class, 'getUserStaff']);
    Route::delete('/remove-staff/{id}', [StaffController::class, 'removeStaff']);
    Route::get('/get-user', [UserController::class, 'getUser']);
    Route::post('/upload-photo', [FilesController::class, 'uploadPhoto']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::post('/update-user', [UserController::class, 'updateUser']);
    Route::post('/user-time-in', [StaffController::class, 'timeIn']);

    Route::get('/get-user-records', [StaffController::class, 'getUserRecords']);

    Route::post('/get-user-dtr', [StaffController::class, 'getUserDTR']);

    Route::get('/user-dropdown', [StaffController::class, 'getUserDropdown']);

  

    //SERVICES
    Route::post('/create-services', [ServicesController::class, 'createServices']);
    Route::post('/create-services-category', [ServicesController::class, 'createServiceCategory']);
    Route::post('/get-service-category', [ServicesController::class, 'getServiceCategory']);
    

    Route::delete('/remove-service/{id}', [ServicesController::class, 'removeSevice']);
    Route::delete('/remove-service-category/{id}', [ServicesController::class, 'removeSeviceCategory']);
    Route::put('/update-service', [ServicesController::class, 'updateServices']);

    Route::put('/update-service-category', [ServicesController::class, 'updateServicesCategory']);
    Route::get('/get-service-category-dropdown', [ServicesController::class, 'getServiceCategoryDropdown']);
   
    //PRODUCT
    Route::post('/create-product', [ProductController::class, 'createProduct']);
    Route::post('/get-products', [ProductController::class, 'getProducts']);
    Route::delete('/remove-product/{id}', [ProductController::class, 'removeProduct']);
    Route::put('/update-product', [ProductController::class, 'updateProduct']);

    Route::get('/get-services-dropdown', [ServicesController::class, 'getServicesDropdown']);
    Route::get('/get-products-dropdown', [ServicesController::class, 'getProductsDropDown']);

    Route::post('/create-appointment', [AppointmentController::class, 'createAppointment']);
    Route::get('/get-appointment', [AppointmentController::class, 'getUserAppointment']);

    Route::post('/attach-service-items', [ServicesController::class, 'createServiceItems']);
    Route::post('/remove-service-items', [ServicesController::class, 'removeSeviceItems']);

    Route::get('/get-all-appointments', [AppointmentController::class, 'getAllAppointments']);
    Route::post('/get-status-appointments', [AppointmentController::class, 'getStatusAppointments']);
    Route::post('/update-status-appointment', [AppointmentController::class, 'updateAppointment']);
    
    Route::post('/appointment-otp', [SmsController::class, 'sendSms']);
    Route::post('/submit-appointment-otp', [SmsController::class, 'VerifyOtp']);

    Route::post('/update-notifications', [NotificationsController::class, 'UpdateNotification']);
    Route::get('/get-notifications', [NotificationsController::class, 'NotificationData']);
   

    Route::post('/assign-staff-services', [StaffController::class, 'AssignStaffServices']);
    Route::delete('/remove-staff-services/{id}', [StaffController::class, 'removeStaffServices']);

    Route::post('/send-feedback', [ReviewsController::class, 'storeUserFeedback']);

    //SUPPORT
    Route::post('/send-support', [SupportController::class, 'SendSupport']);
    Route::post('/get-all-support', [SupportController::class, 'fetchAllSupport']);
   
});



Route::post('/get-services', [ServicesController::class, 'getServices']);
Route::get('/get-staff', [StaffController::class, 'getStaffDetails']);
Route::post('/book-staff-dropdown', [StaffController::class, 'getStaffServiceDropdown']);

Route::post('/get-otp', [SmsController::class, 'sendSms']);
Route::post('/submit-user-otp', [SmsController::class, 'VerifyOtp']);


Route::post('/submit-recovery-email', [AuthController::class, 'recoverAccount']);

Route::post('/get-recovery-otp', [SmsController::class, 'getRecoveryOTP']);

Route::post('/recovery-change-password', [AuthController::class, 'recoveryChangePassword']);

Route::post('/get-schedule-appointment', [AppointmentController::class, 'getScheduledAppointment']);

Route::post('/get-all-reviews', [ReviewsController::class, 'getallReviews']);

Route::get('send-mail', [MailController::class, 'index']);


Route::post('/validate-account', [AuthController::class, 'validateAccount']);
