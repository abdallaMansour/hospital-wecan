<?php

use Illuminate\Support\Facades\Route;
use App\Filament\Resources\ChatResource\Pages\CustomChatPage;
use Illuminate\Support\Facades\Auth;

Route::post('/translate-message', [CustomChatPage::class, 'translateMessage'])->middleware('auth');

// Temporary debugging route for HospitalUserAttachment
Route::get('/debug/connection-requests', function () {
    $user = Auth::user();
    $authAccount = null;
    $authAccountType = null;
    
    if ($user->account_type === 'user') {
        $authAccount = $user->parent ?? $user;
    } else {
        $authAccount = $user;
    }
    
    $authAccountType = $authAccount->account_type;
    
    $query = null;
    if ($authAccountType === 'hospital') {
        $query = \App\Models\HospitalUserAttachment::where('hospital_id', $authAccount->hospital_id);
    } elseif ($authAccountType === 'doctor') {
        $query = \App\Models\HospitalUserAttachment::where('doctor_id', $authAccount->id);
    } else {
        $query = \App\Models\HospitalUserAttachment::where(function ($q) use ($authAccount) {
            $q->where('sender_id', $authAccount->id)
              ->orWhere('user_id', $authAccount->id)
              ->orWhere('doctor_id', $authAccount->id);
        });
    }
    
    $records = $query->get();
    
    return response()->json([
        'user_id' => $user->id,
        'user_account_type' => $user->account_type,
        'auth_account_id' => $authAccount->id,
        'auth_account_type' => $authAccountType,
        'records_count' => $records->count(),
        'records' => $records->toArray(),
        'sql' => $query->toSql(),
        'bindings' => $query->getBindings()
    ]);
})->middleware('auth');

// Temporary debugging route to test updating a specific record
Route::post('/debug/update-connection-request/{id}', function ($id) {
    $record = \App\Models\HospitalUserAttachment::find($id);
    
    if (!$record) {
        return response()->json(['error' => 'Record not found'], 404);
    }
    
    $status = request('status', 'approved');
    
    // Log before update
    \Illuminate\Support\Facades\Log::info('Debug update - before', [
        'record_id' => $record->id,
        'record_data' => $record->toArray(),
        'new_status' => $status
    ]);
    
    $record->update(['status' => $status]);
    
    // Log after update
    \Illuminate\Support\Facades\Log::info('Debug update - after', [
        'record_id' => $record->id,
        'record_data' => $record->fresh()->toArray()
    ]);
    
    return response()->json([
        'success' => true,
        'record' => $record->fresh()->toArray()
    ]);
})->middleware('auth');
