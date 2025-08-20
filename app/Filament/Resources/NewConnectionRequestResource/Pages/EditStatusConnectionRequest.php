<?php

namespace App\Filament\Resources\NewConnectionRequestResource\Pages;

use App\Filament\Resources\NewConnectionRequestResource;
use App\Models\Hospital;
use App\Models\HospitalUserAttachment;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;

class EditStatusConnectionRequest extends EditRecord
{
    protected static string $resource = NewConnectionRequestResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('status')
                    ->options([
                        'pending' => __('dashboard.pending'),
                        'approved' => __('dashboard.approved'),
                        'rejected' => __('dashboard.rejected'),
                    ])
                    ->required(),
            ]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $data = $this->form->getState();

        if ($data['status'] === 'approved') {
            $authentication_type = Auth::user()->account_type;
            $user = null;

            if ($authentication_type === 'doctor') {
                if ($record->user_id) {
                    $user = User::find($record->user_id);
                } elseif ($record->hospital_id) {
                    $hospital = Hospital::find($record->hospital_id);
                    $user = $hospital ? $hospital->user : null;
                }
            } elseif ($authentication_type === 'hospital') {
                if ($record->doctor_id) {
                    $user = User::find($record->doctor_id);
                } elseif ($record->user_id) {
                    $user = User::find($record->user_id);
                }
            }
            
            if ($user) {
                $user->update(['parent_id' => Auth::id()]);
            }
        }

        Notification::make()
            ->title(__('dashboard.status_updated_successfully'))
            ->success()
            ->send();
    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
