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

class MessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'messages';

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
                        $userName = $record->user?->name ?? 'User';
                        $time = optional($record->created_at)?->format('H:i');

                        return "<div class='flex {$alignmentClass} w-full'>
                                    <div class='max-w-[70%] rounded-2xl px-3 py-2 {$bubbleClass} shadow'>
                                        <div class='text-sm whitespace-pre-wrap'>" . e($state) . "</div>
                                        <div class='mt-1 text-[10px] opacity-70'>" . e($time) . "</div>
                                    </div>
                                </div>";
                    })
                    ->html()
                    ->extraAttributes(fn (ChatMessage $record): array => [
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
                            ->required()
                            ->autosize()
                            ->maxLength(2000),
                        Forms\Components\Hidden::make('user_id')->default(Auth::id()),
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
