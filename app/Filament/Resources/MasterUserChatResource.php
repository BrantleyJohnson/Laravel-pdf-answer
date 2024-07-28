<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterUserChatResource\Pages;
use App\Filament\Resources\MasterUserChatResource\RelationManagers;
use App\Models\MasterUserChat;
use App\Models\UserChat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class MasterUserChatResource extends Resource
{
    protected static ?string $model = MasterUserChat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('created_at')->required(),
                Forms\Components\Select::make('id')
                    ->label('Thread')
                    ->options(function (MasterUserChat $masterUserChat): array {
                        $userChats = UserChat::where('master_user_chat_id', $masterUserChat->id)->get()->toArray();
                        $options = array_map(function($uc){
                            return [
                               // 'value' => $uc['id'],
                                'label' => $uc['question'] . '  :  ' . $uc['answer']
                            ];
                        }, $userChats);
                        return $options;
                    })
                    ->required()
                    ->searchable(), 
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('chatgpt_id'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                            ->url(fn (MasterUserChat $record): string => route('adminview', $record))
                            ->openUrlInNewTab()
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
            'index' => Pages\ListMasterUserChats::route('/'),
            'create' => Pages\CreateMasterUserChat::route('/create'),
            'edit' => Pages\EditMasterUserChat::route('/{record}/edit'),
        ];
    }
}
