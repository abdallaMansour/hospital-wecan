<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Resources\DoctorProfileResource;
use App\Http\Resources\PatientProfileResource;
use App\Models\Hospital;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'fcm_token' => 'required|string', // Add this line to validate FCM token
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if ($user && !in_array($user->account_status, ['cancelled', 'banned'])) {
            if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
                $user = Auth::user();

                // Update FCM token
                $user->fcm_token = $validatedData['fcm_token'];
                $user->save();

                $token = $user->createToken('user_api')->plainTextToken;

                $responseData = [
                    'id' => $user->id,
                    'token' => $token,
                    'email' => $user->email,
                    'name' => $user->name,
                    'preferred_language' => $user->preferred_language,
                    'account_type' => $user->account_type,
                    'fcm_token' => $user->fcm_token, // Add FCM token to response data
                ];

                // Add account status if the user is a hospital
                if ($user->account_type === 'hospital') {
                    $responseData['account_status'] = $user->hospital->account_status;
                }

                return ResponseHelper::success('Login successful', $responseData);
            } else {
                return ResponseHelper::error('Invalid credentials', 401);
            }
        } else {
            return ResponseHelper::error('Account is cancelled or banned', 401);
        }
    }
    public function logout(Request $request)
    {
        $user = Auth::user();

        $user->tokens()->delete();
        return ResponseHelper::success('Logout successful');
    }

    public function registerPatient(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|min:4|max:4',
                'preferred_language' => 'required',
                'country_id' => 'required|exists:countries,id',
            ]);
        } catch (ValidationException $e) {
            return ResponseHelper::error($e->errors(), 422);
        }

        try {
            // Create a new user instance
            $user = new User();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->account_type =  'patient';
            $user->country_id =  $validatedData['country_id'];
            $user->preferred_language =  $validatedData['preferred_language'];

            $user->save();

            $token = $user->createToken('user_api')->plainTextToken;

            $user->setAttribute('token', $token);

            return ResponseHelper::success('User registered successfully', new UserResource($user), 201);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while registering user');
        }
    }

    public function registerDoctor(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users|max:255',
                'password' => 'required|string|min:4|max:4',
                'preferred_language' => 'required',
                'profession_ar' => 'nullable|string',
                'profession_en' => 'nullable|string',
                'hospital_ar' => 'nullable|string',
                'hospital_en' => 'nullable|string',
                'contact_number' => 'nullable|string',
                'experience_years' => 'nullable|numeric',
                'show_info_to_patients' => 'required',
                'profile_picture' => 'nullable|file'
            ]);
        } catch (ValidationException $e) {
            return ResponseHelper::error($e->errors(), 422);
        }

        try {
            // Create a new user instance
            $user = new User();
            $user->fill(Arr::except($validatedData, ['profile_picture']));
            $user->password = Hash::make($validatedData['password']);
            $user->account_type =  'doctor';

            if ($request->hasFile('profile_picture')) {
                $image = $request->file('profile_picture');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/profile_pictures', $imageName); // Store the image in storage/app/public/drug_images directory
                $user->profile_picture = 'profile_pictures/' . $imageName; // Set the image path in the database
            }

            $user->save();

            $token = $user->createToken('user_api')->plainTextToken;

            $user->setAttribute('token', $token);

            return ResponseHelper::success('User registered successfully', new UserResource($user), 201);
        } catch (\Exception $e) {
            Log::error('Error registering user: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while registering user');
        }
    }
    public function registerHospital(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'hospital_name' => 'required|string|max:255',
                'hospital_logo' => 'nullable|file',
                'user_name' => 'required|string|max:255',
                'email' => 'required|email|unique:hospitals|max:255',
                'password' => 'required|string|min:4|max:8',
                'contact_number' => 'required|string',
                'country_id' => 'required|exists:countries,id',
                'city' => 'required|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return ResponseHelper::error($e->errors(), 422);
        }

        try {
            Log::info(message: 'Validated data for hospital registration:', context: $validatedData);

            $hospital = new Hospital();
            $hospital->hospital_name = $validatedData['hospital_name'];
            $hospital->user_name = $validatedData['user_name'];
            $hospital->email = $validatedData['email'];
            $hospital->contact_number = $validatedData['contact_number'];
            $hospital->country_id = $validatedData['country_id'];
            $hospital->city = $validatedData['city'];
            //$hospital->account_status = 'pending'; // Set the account status to pending

            if ($request->hasFile('hospital_logo')) {
                $logo = $request->file('hospital_logo');
                $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                $logo->storeAs('public/hospital_logos', $logoName);
                $hospital->hospital_logo = 'hospital_logos/' . $logoName;
            }
            $hospital->save();

            $user = new User();
            $user->name = $validatedData['user_name'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->account_type = 'hospital';
            //  $user->account_status = 'pending'; // Set the account status to pending
            $user->hospital_id = $hospital->id;

            $user->save();

            $token = $user->createToken('user_api')->plainTextToken;

            $user->setAttribute('token', $token);

            return ResponseHelper::success('Hospital registered successfully. Account is pending approval.', new UserResource($user), 201);
        } catch (\Exception $e) {
            Log::error('Error registering hospital: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('Validated data:', $validatedData);

            return ResponseHelper::error('An error occurred while registering hospital');
        }
    }

    public function profile()
    {
        try {
            $user = Auth::user();
            if ($user->account_type == 'patient') {
                return ResponseHelper::success('Patient Profile successfully', new PatientProfileResource($user), 201);
            }
            if ($user->account_type == 'doctor') {
                return ResponseHelper::success('Doctor Profile successfully', new DoctorProfileResource($user), 201);
            }
            if ($user->account_type == 'hospital') {
                $hospital = $user->hospital;
                $profileData = [
                    'id' => $hospital->id,
                    'hospital_name' => $hospital->hospital_name,
                    'hospital_logo' => $hospital->hospital_logo,
                    'user_name' => $user->name,
                    'email' => $user->email,
                    'contact_number' => $hospital->contact_number,
                    'country_id' => $hospital->country_id,
                    'city' => $hospital->city,
                    'account_type' => $user->account_type,
                    'account_status' => $hospital->account_status,
                ];
                return ResponseHelper::success('Hospital Profile successfully', $profileData, 201);
            }
            return ResponseHelper::error('An error occurred while getting user profile');
        } catch (\Exception $e) {
            Log::error('Error get profile: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while getting user profile');
        }
    }

    public function update(Request $request)
    {
        $id =  Auth::id();
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:4|max:4',
            'country_id' => 'nullable|exists:countries,id',
            'preferred_language' => 'nullable|string|in:ar,en',
            'profession_ar' => 'nullable|string',
            'profession_en' => 'nullable|string',
            'hospital_ar' => 'nullable|string',
            'hospital_en' => 'nullable|string',
            'contact_number' => 'nullable|string',
            'experience_years' => 'nullable|integer',
            'profile_picture' => 'nullable|image',
            'show_info_to_patients' => 'nullable|boolean',
        ]);

        try {
            $user = User::findOrFail($id);

            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->preferred_language = $validatedData['preferred_language'];

            if ($user->account_type === 'patient') {
                $user->country_id = $validatedData['country_id'];
            } elseif ($user->account_type === 'doctor') {
                $user->profession_ar = $validatedData['profession_ar'];
                $user->profession_en = $validatedData['profession_en'];
                $user->hospital_ar = $validatedData['hospital_ar'];
                $user->hospital_en = $validatedData['hospital_en'];
                $user->contact_number = $validatedData['contact_number'];
                $user->experience_years = $validatedData['experience_years'];
                $user->show_info_to_patients = $validatedData['show_info_to_patients'];
            } elseif ($user->account_type === 'hospital') {
                $hospital = $user->hospital;
                $hospital->hospital_name = $validatedData['hospital_name'];
                $hospital->contact_number = $validatedData['contact_number'];
                $hospital->country_id = $validatedData['country_id'];
                $hospital->city = $validatedData['city']; // Changed from city_id to city

                if ($request->hasFile('hospital_logo')) {
                    $logo = $request->file('hospital_logo');
                    $logoName = time() . '_' . uniqid() . '.' . $logo->getClientOriginalExtension();
                    $logo->storeAs('public/hospital_logos', $logoName); // Ensure the directory is correct
                    $hospital->hospital_logo = 'hospital_logos/' . $logoName;
                }

                $hospital->save();
            }

            if ($request->filled('password')) {
                $user->password = Hash::make($validatedData['password']);
            }

            if ($request->hasFile('profile_picture')) {
                $profilePicture = $request->file('profile_picture');
                $imageName = time() . '_' . uniqid() . '.' . $profilePicture->getClientOriginalExtension();
                $profilePicture->storeAs('public/profile_pictures', $imageName);
                $user->profile_picture = 'profile_pictures/' . $imageName;
            }

            $user->save();

            if ($user->account_type == 'patient') {
                return ResponseHelper::success('Patient Profile updated successfully', new PatientProfileResource($user), 201);
            }
            if ($user->account_type == 'doctor') {
                return ResponseHelper::success('Doctor Profile updated successfully', new DoctorProfileResource($user), 201);
            }
            if ($user->account_type == 'hospital') {
                return ResponseHelper::success('Hospital Profile updated successfully', new UserResource($user->hospital), 201);
            }
        } catch (\Exception $e) {
            Log::error('Error get profile: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while updating user profile');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function forgetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $newPassword = str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        $user->password = Hash::make($newPassword);
        $user->save();

        Mail::to($user->email)->send(new VerifyEmail($newPassword));


        return  ResponseHelper::success('A new password has been sent to your email address.');
    }
    public function getPatientByEmail(Request $request)
    {
        // Ensure the user is authenticated and is a hospital
        if (!Auth::check() || Auth::user()->account_type !== 'hospital') {
            return ResponseHelper::error('Unauthorized. Only hospitals can access this information.', 403);
        }

        // Validate the request
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        try {
            // Find the patient by email
            $patient = User::where('email', $validatedData['email'])
                ->where('account_type', 'patient')
                ->first();

            if (!$patient) {
                return ResponseHelper::error('Patient not found or email does not belong to a patient account.', 404);
            }

            // Return the patient profile
            return ResponseHelper::success('Patient profile retrieved successfully', new PatientProfileResource($patient));
        } catch (\Exception $e) {
            Log::error('Error retrieving patient profile: ' . $e->getMessage());
            return ResponseHelper::error('An error occurred while retrieving the patient profile', 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function deleteUser()
    {
        try {
            // Get the currently authenticated user.
            $user = Auth::user();

            if (!$user) {
                // User not found in authentication context.
                return ResponseHelper::error('User not found', 404);
            }

            // Delete user's tokens (logout).
            $user->tokens()->delete();

            $user->healthTips()->delete();

            // Delete the user.
            $user->delete();

            // Return a success response.
            return ResponseHelper::success('User deleted and logged out successfully');
        } catch (\Exception $e) {
            // Handle other errors.
            Log::error('Error deleting user: ' . $e->getMessage());

            return ResponseHelper::error('An error occurred while deleting the user', 500);
        }
    }
}
