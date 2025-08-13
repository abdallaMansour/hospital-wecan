<?php

namespace App\Filament\Resources\ChatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ChatMessage;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('message')
                    ->nullable()
                    ->maxLength(255)
                    ->rule('required_without:attachment_path'),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
                Forms\Components\Hidden::make('chat_room_id')
                    ->default(fn () => $this->getOwnerRecord()->id),
                FileUpload::make('attachment_path')
                    ->label('Attachment')
                    ->nullable()
                    ->acceptedFileTypes([
                        'image/*', 'video/*',
                        'application/pdf',
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        'text/plain'
                    ])
                    ->directory('chat-attachments')
                    ->rule('required_without:message'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'asc')
            ->recordTitleAttribute('message')
            ->columns([
                Tables\Columns\TextColumn::make('message')
                    ->label('')
                    ->formatStateUsing(function (string $state, ChatMessage $record): string {
                        $isMe = (int) $record->user_id === (int) Auth::id();
                        $alignmentClass = $isMe ? 'justify-end' : 'justify-start';
                        $bubbleClass = $isMe
                            ? 'bg-primary-600 text-white'
                            : 'bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-gray-100';
                        $time = optional($record->created_at)?->format('H:i');

                        // لو بتخزن المسار على disk عام، استخدم Storage::url
                        $url = $record->attachment_path ? Storage::url($record->attachment_path) : null;

                        $content = match ($record->message_type) {
                            'text' => "<div class='text-sm whitespace-pre-wrap'>" . e($record->message) . "</div>",
                            'image' => "<img src='" . e($url) . "' alt='Image' class='max-w-full h-auto rounded' style='max-height: 200px;'><span class='text-sm whitespace-pre-wrap'>" . e($record->message) . "</span>",
                            'video' => "<video controls class='max-w-full h-auto rounded' style='max-height: 200px;'><source src='" . e($url) . "' type='video/mp4'>Your browser does not support the video tag.</video><span class='text-sm whitespace-pre-wrap'>" . e($record->message) . "</span>",
                            default => "<a href='" . e($url) . "' download class='block text-blue-500 hover:text-blue-700 border-none' style='background-color: #1a2e3c;padding: 20px'>📄 Download Document</a><span class='text-sm whitespace-pre-wrap'>" . e($record->message) . "</span>",
                        };

                        return "<div class='flex {$alignmentClass} w-full'>
                                    <div class='max-w-[70%] rounded-2xl px-3 py-2 {$bubbleClass} shadow'>
                                        {$content}
                                        <div class='mt-1 text-[10px] opacity-70'>" . e($time) . "</div>
                                    </div>
                                </div>";
                    })
                    ->html()
                    ->extraAttributes(fn(ChatMessage $record): array => [
                        'class' => 'align-top !p-1',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Send')
                    ->modalHeading('Send a message')
                    ->form([
                        Textarea::make('message')
                            ->placeholder('Type a message...')
                            ->nullable()
                            ->autosize()
                            ->maxLength(2000)
                            ->rule('required_without:attachment_path'),
                        FileUpload::make('attachment_path')
                            ->label('Attachment')
                            ->nullable()
                            ->acceptedFileTypes([
                                'image/*', 'video/*',
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'text/plain'
                            ])
                            ->directory('chat-attachments')
                            ->rule('required_without:message'),
                        Forms\Components\Hidden::make('user_id')->default(Auth::id()),
                        Forms\Components\Hidden::make('chat_room_id')->default(fn() => $this->getOwnerRecord()->id),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
