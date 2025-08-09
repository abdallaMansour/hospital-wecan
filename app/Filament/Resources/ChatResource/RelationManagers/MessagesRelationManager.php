<?php

namespace App\Filament\Resources\ChatResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\ChatMessage;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\FileUpload;

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

    public function booted(): void
    {
        $owner = $this->getOwnerRecord();
        if ($owner) {
            ChatMessage::where('chat_room_id', $owner->id)
                ->where('user_id', '!=', Auth::id())
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('message')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Hidden::make('user_id')
                    ->default(Auth::id()),
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

                        // Blade components don't render here; use plain HTML
                        $readStatus = '';
                        if ($isMe) {
                            if ((bool) $record->is_read) {
                                $readStatus = "<span class='ml-1 text-success-500 text-[10px]'>✓✓</span>";
                            } else {
                                $readStatus = "<span class='ml-1 text-gray-400 text-[10px]'>✓</span>";
                            }
                        }


                        $contentHtml = '';
                        if ($record->message_type === 'media') {
                            $path = $record->attachment_path ?: $record->message;
                            $escapedPath = e($path);
                            $url = $record->attachment_path;
                            $escapedUrl = e($url);
                            $contentHtml = "<a href='{$escapedUrl}' target='_blank' class='block'>";
                            if (preg_match('/\.(png|jpg|jpeg|gif|webp)$/i', $path)) {
                                $contentHtml .= "<img src='{$escapedUrl}' alt='' class='rounded-md max-h-64' width='100'>";
                            } elseif (preg_match('/\.(mp4|webm|ogg)$/i', $path)) {
                                $contentHtml .= "<video controls class='rounded-md max-h-64'><source src='{$escapedUrl}'></video>";
                            } elseif (preg_match('/\.(mp3|wav|ogg)$/i', $path)) {
                                $contentHtml .= "<audio controls class='w-full'><source src='{$escapedUrl}'></audio>";
                            } else {
                                $contentHtml .= "<span class='underline'>" . basename($escapedPath) . "</span>";
                            }
                            $contentHtml .= '</a>';
                        } else {
                            $contentHtml = "<div class='text-sm whitespace-pre-wrap'>" . e($state) . "</div>";
                        }

                        $margin_style = $isMe ? 'margin-left: 700px;' : '';
                        return "<div class='flex {$alignmentClass} w-full' style='{$margin_style}'>
                                    <div class='max-w-[70%] rounded-2xl px-3 py-2 {$bubbleClass} shadow'>
                                        {$contentHtml}
                                        <div class='mt-1 text-[10px] opacity-70'>" . e($time) . " " . $readStatus . "</div>
                                    </div>
                                </div>";
                    })
                    ->html()
                    ->extraAttributes(fn(ChatMessage $record): array => [
                        'class' => 'align-top !p-1',
                    ]),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Send')
                    ->modalHeading('Send a message')
                    ->form([
                        Textarea::make('message')
                            ->placeholder('Type a message...')
                            ->autosize()
                            ->maxLength(2000),
                        FileUpload::make('attachment')
                            ->label('Media')
                            ->directory('chat/attachments')
                            ->imageEditor()
                            ->previewable(true)
                            ->openable()
                            ->downloadable()
                            ->maxSize(2048)
                            ->acceptedFileTypes(['image/*', 'video/*', 'audio/*'])
                            ->nullable(),
                        Forms\Components\Hidden::make('user_id')->default(Auth::id()),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['message_type'] = ! empty($data['attachment']) ? 'media' : 'text';
                        return $data;
                    })
                    ->using(function (array $data, RelationManager $livewire) {
                        $parent = $livewire->getOwnerRecord();
                        $message = new ChatMessage();
                        $message->chat_room_id = $parent->id;
                        $message->user_id = $data['user_id'];
                        $message->message = $data['message'] ?? '';
                        $message->message_type = $data['message_type'] ?? 'text';
                        // Persist file path in message when media exists
                        if (! empty($data['attachment'])) {
                            $path = is_array($data['attachment']) ? ($data['attachment'][0] ?? '') : $data['attachment'];
                            $message->attachment_path = $path;
                        }
                        $message->save();
                        return $message;
                    }),
            ]);
    }
}
