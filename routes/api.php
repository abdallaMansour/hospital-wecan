<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CancerScreeningCenterController;
use App\Http\Controllers\CharityController;
use App\Http\Controllers\ChemotherapySessionController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HealthTipController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InitiatorController;
use App\Http\Controllers\PatientAppointmentController;
use App\Http\Controllers\PatientFoodController;
use App\Http\Controllers\PatientHealthReportController;
use App\Http\Controllers\PatientMedicationController;
use App\Http\Controllers\PatientNoteController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\SupporterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\FavoriteController;

use App\Http\Controllers\ChatController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
Route::post('/register-patient', [AuthController::class, 'registerPatient']);
Route::post('/register-doctor', [AuthController::class, 'registerDoctor']);
Route::post('/register-hospital', [AuthController::class, 'registerHospital']); // Added route for registering hospital

Route::post('/supporters', [SupporterController::class, 'addSupporter']);
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/regions', [RegionController::class, 'index']);
Route::get('/supporters', [SupporterController::class, 'index']);
Route::get('/initiators', [InitiatorController::class, 'index']);
Route::get('/doctors', [DoctorController::class, 'index']);
Route::post('/contact-us', [ContactUsController::class, 'store']);
Route::get('/cancer-screening-centers', [CancerScreeningCenterController::class, 'index']);
Route::get('/charities', [CharityController::class, 'index']);

Route::post('/chat/create-room', [ChatController::class, 'createRoom']);

Route::middleware('auth:sanctum')->group(function () {
  
    Route::get('/logout', [AuthController::class, 'logout']);
    Route::delete('/user', [AuthController::class, 'deleteUser']);

    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/profile', [AuthController::class, 'update']);
    Route::get('/home', [HomeController::class, 'patientHome']);
    Route::get('/get-patient-by-email', [AuthController::class, 'getPatientByEmail']);

    Route::apiResource('/medications', PatientMedicationController::class);
    Route::apiResource('/chemo-sessions', ChemotherapySessionController::class);
    Route::apiResource('/appointments', PatientAppointmentController::class);
    Route::apiResource('/food', PatientFoodController::class);
    Route::apiResource('/notes', PatientNoteController::class);
    Route::apiResource('/health-reports', PatientHealthReportController::class);
    Route::get('/chat/user-rooms', [ChatController::class, 'getUserRooms']);
    Route::apiResource('/health-tips', HealthTipController::class);
    Route::post('/chat/send-message', [ChatController::class, 'sendMessage']);
    Route::get('/chat/room-messages', [ChatController::class, 'getRoomMessages']);
    Route::post('/attach/doctor-to-hospital', [AttachmentController::class, 'attachDoctorToHospital']);
    Route::post('/attach/patient-to-hospital', [AttachmentController::class, 'attachPatientToHospital']);
    Route::post('/attach/patient-to-doctor', [AttachmentController::class, 'attachPatientToDoctor']);
    Route::post('/attach/hospital-to-patient', [AttachmentController::class, 'attachHospitalToPatient']);
    Route::post('/approve-attachment', [AttachmentController::class, 'approveAttachment']);
    Route::get('/attachments', [AttachmentController::class, 'getAttachments']);
    Route::delete('/delete-attachment', [AttachmentController::class, 'deleteAttachment']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
    Route::get('/patient-health-reports/{email}', [PatientHealthReportController::class, 'getPatientHealthReports']);
    Route::get('/patient-foods/{email}', [PatientFoodController::class, 'getPatientFoods']);
    Route::get('/patient-medications/{email}', [PatientMedicationController::class, 'getPatientMedications']);
    Route::get('/patient-appointments/{email}', [PatientAppointmentController::class, 'getPatientAppointments']);
    Route::get('/patient-chemo-sessions/{email}', [ChemotherapySessionController::class, 'getPatientChemoSessions']);
    Route::post('/hospital-medications', [PatientMedicationController::class, 'storeByHospital']);
    Route::put('/hospital/medications/{id}', [PatientMedicationController::class, 'updateByHospital']);
    
    Route::post('/hospital-chemo-sessions', [ChemotherapySessionController::class, 'storeByHospital']);
    Route::put('/hospital/chemo-sessions/{id}', [ChemotherapySessionController::class, 'updateByHospital']);
    
        Route::post('/hospital-appointments', [PatientAppointmentController::class, 'storeByHospital']);
    Route::put('/hospital/appointments/{id}', [PatientAppointmentController::class, 'updateByHospital']);
    Route::post('/patient-foods/hospital', [PatientFoodController::class, 'storeByHospital'])->middleware('auth:sanctum');
Route::put('/patient-foods/{id}/hospital', [PatientFoodController::class, 'updateByHospital'])->middleware('auth:sanctum');
    Route::post('/hospital/patient-health-reports', [PatientHealthReportController::class, 'storeByHospital'])->middleware('auth:sanctum');
Route::put('/hospital/patient-health-reports/{id}', [PatientHealthReportController::class, 'updateByHospital'])->middleware('auth:sanctum');
});
