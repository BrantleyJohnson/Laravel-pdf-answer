<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatUserDislikeResource\Pages;
use App\Filament\Resources\ChatUserDislikeResource\RelationManagers;
use App\Models\ChatUserDislike;
use App\Models\User;
use App\Models\UserChat;
use App\Models\PdfUserChat;
use App\Models\Pdf;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Actions\Action;
use App\Jobs\UploadFileJob;

class ChatUserDislikeResource extends Resource
{
    protected static ?string $model = ChatUserDislike::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('comment')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('created_at')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('user_chat_id')
                    ->label('Thread')
                    ->options(
                    function (ChatUserDislike $chatUserDislike): array {
                        $id = UserChat::where('id', $chatUserDislike->user_chat_id)->first()->master_user_chat_id;
                        $userChats = UserChat::where('master_user_chat_id', $id)->get()->toArray();
                        $options = array_map(function($uc){
                            return [
                                'value' => 'Question-> '.$uc['question'],
                                'label' => 'Answer->  '.$uc['answer'],
                            ];
                        }, $userChats);
                        return $options;
                    }
                        )
                    ->hintActions([
                            Action::make('Accept')
                            ->action(function (ChatUserDislike $chatUserDislike): void {
                                $pdfId = PdfUserChat::where('user_chat_id', $chatUserDislike->user_chat_id)->first()->pdf_id;
                                $chatgpt_file_id = Pdf::where('id', $pdfId)->first()->chatgpt_file_id;
                                 UploadFileJob::dispatch($pdfId, $chatgpt_file_id);
                                 
                            })
                            ->requiresConfirmation()
                            ->modalHeading('Accept')
                            ->modalDescription('Are you sure you\'d like to Accept this thread?')
                            ->modalSubmitActionLabel('Yes, accept it'),
                            Action::make('Reject')
                                ->action(
                                   function (ChatUserDislike $chatUserDislike): void {
                                    $chatUserDislike -> delete();
                                   }
                                )
                                ->requiresConfirmation()
                                ->modalHeading('Reject')
                                ->modalDescription('Are you sure you\'d like to reject this thread?')
                                ->modalSubmitActionLabel('Yes, reject it'),
                    ])
                    ->required()
                    ->searchable(),
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('comment'),
                Tables\Columns\TextColumn::make('userchat.answer'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn (ChatUserDislike $record): string => route('dislike', UserChat::where('id', $record->user_chat_id)->first()->master_user_chat_id))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
               
               
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatUserDislikes::route('/'),
            'create' => Pages\CreateChatUserDislike::route('/create'),
            'edit' => Pages\EditChatUserDislike::route('/{record}/edit'),
        ];
    }
}
