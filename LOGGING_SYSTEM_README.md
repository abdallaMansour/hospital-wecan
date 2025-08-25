# Logging System Implementation

This document explains the logging system that has been implemented to track which user created or modified records in the hospital management system.

## Overview

The logging system adds a `log_user_id` column to all relation tables to track which user (doctor, hospital admin, etc.) created or modified each record. This provides an audit trail for accountability and compliance purposes.

## Tables with Logging

The following tables now have `log_user_id` columns:

1. `health_tips` - Health tips created by doctors
2. `patient_medications` - Patient medications prescribed by doctors
3. `chemotherapy_sessions` - Chemotherapy sessions scheduled by doctors
4. `patient_appointments` - Patient appointments created by doctors
5. `patient_food` - Patient food recommendations by doctors
6. `patient_health_reports` - Patient health reports created by doctors
7. `patient_notes` - Patient notes created by doctors

## Database Schema

Each table now includes:
```sql
log_user_id BIGINT UNSIGNED NULL
FOREIGN KEY (log_user_id) REFERENCES users(id) ON DELETE SET NULL
```

## Models Updated

All corresponding models have been updated with:

1. **Fillable arrays** - Include `log_user_id` for mass assignment
2. **Relationships** - Added `logUser()` method to access the user who created/modified the record
3. **Loggable trait** - Automatically sets `log_user_id` on create/update operations

## Usage Examples

### Automatic Logging

The `Loggable` trait automatically sets the `log_user_id` when creating or updating records:

```php
// This will automatically set log_user_id to the authenticated user's ID
$healthTip = HealthTip::create([
    'title_en' => 'New Health Tip',
    'details_en' => 'Tip details...',
    'user_id' => $patientId,
    // log_user_id will be set automatically
]);
```

### Manual Logging

You can also manually set the `log_user_id`:

```php
$healthTip = new HealthTip();
$healthTip->title_en = 'New Health Tip';
$healthTip->user_id = $patientId;
$healthTip->log_user_id = Auth::id(); // or any user ID
$healthTip->save();
```

### Querying Logged Records

#### Get records created by a specific user:
```php
// Get all health tips created by a specific doctor
$doctor = User::find($doctorId);
$healthTips = $doctor->loggedHealthTips;

// Or query directly
$healthTips = HealthTip::where('log_user_id', $doctorId)->get();
```

#### Get the user who created a record:
```php
$healthTip = HealthTip::find($id);
$creator = $healthTip->logUser; // Returns User model
```

#### Get all records created by the authenticated user:
```php
$myRecords = HealthTip::where('log_user_id', Auth::id())->get();
```

## User Model Relationships

The `User` model now includes relationships to access all records created by a user:

```php
// Get all records created by a user
$user = User::find($userId);

$user->loggedHealthTips;           // Health tips created by this user
$user->loggedPatientMedications;   // Medications prescribed by this user
$user->loggedChemotherapySessions; // Chemo sessions scheduled by this user
$user->loggedPatientAppointments;  // Appointments created by this user
$user->loggedPatientFoods;         // Food recommendations by this user
$user->loggedPatientHealthReports; // Health reports by this user
$user->loggedPatientNotes;         // Notes created by this user
```

## API Integration

When creating records via API, the `log_user_id` will be automatically set to the authenticated user's ID if the `Loggable` trait is used.

## Filament Admin Panel

In the Filament admin panel, you can now see and filter records by the user who created them. The `log_user_id` field will be automatically populated when creating or editing records through the admin interface.

## Migration Files

The following migration files were created:
- `2025_08_25_122259_add_log_user_id_to_health_tips_table.php`
- `2025_08_25_122306_add_log_user_id_to_patient_medications_table.php`
- `2025_08_25_122324_add_log_user_id_to_chemotherapy_sessions_table.php`
- `2025_08_25_122334_add_log_user_id_to_patient_appointments_table.php`
- `2025_08_25_122341_add_log_user_id_to_patient_food_table.php`
- `2025_08_25_122349_add_log_user_id_to_patient_health_reports_table.php`
- `2025_08_25_122358_add_log_user_id_to_patient_notes_table.php`

## Benefits

1. **Audit Trail** - Track who created or modified each record
2. **Accountability** - Doctors and hospital staff are accountable for their actions
3. **Compliance** - Meets healthcare compliance requirements for record tracking
4. **Analytics** - Analyze which doctors create the most records
5. **Security** - Track unauthorized changes or access

## Notes

- The `log_user_id` is nullable to handle cases where records might be created by system processes
- Foreign key constraint uses `ON DELETE SET NULL` to prevent orphaned records
- The `Loggable` trait only sets `log_user_id` when a user is authenticated
- Existing records will have `log_user_id` as NULL until they are updated

## Troubleshooting

### SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'user_id' cannot be null

If you encounter this error when creating records through Filament relation managers, it means the `user_id` field is being set to null. This can happen if:

1. The `HospitalUserAttachment` record doesn't have a valid `user_id`
2. The relationship definition is incorrect

**Solution:**
- Ensure that the `HospitalUserAttachment` record has a valid `user_id` field
- Check that the relationship definitions in the `HospitalUserAttachment` model are correct
- All relation managers now include validation to prevent null `user_id` values

**Fixed Relationships:**
```php
// In HospitalUserAttachment model
public function healthTips()
{
    return $this->hasMany(HealthTip::class, 'user_id', 'user_id');
}

public function patientMedications()
{
    return $this->hasMany(PatientMedications::class, 'user_id', 'user_id');
}

// ... other relationships follow the same pattern
```

**Validation Added:**
All relation managers now include validation to ensure `user_id` is not null before creating records.
