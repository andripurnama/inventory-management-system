<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JournalResource\Pages;
use App\Filament\Resources\JournalResource\RelationManagers;
use App\Models\Account;
use App\Models\Item;
use App\Models\Journal;
use Filament\Actions\DeleteAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class JournalResource extends Resource
{
    protected static ?string $model = Journal::class;

    protected static ?string $navigationIcon = 'heroicon-c-document-check';

    protected static ?string $navigationGroup = 'Finance';

    protected ?bool $hasDatabaseTransactions = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->native(false)
                    ->displayFormat('d F Y')
                    ->locale('id'),
                Textarea::make('remarks')->required(),
                TableRepeater::make('journalDetails')
                    ->relationship()
                    ->label('Journal Detail')
                    ->schema([
                        Forms\Components\Select::make('account_id')
                            ->relationship('account', 'name')
                            ->label('Akun')
                            ->options(Account::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('debit')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal'),
                        TextInput::make('credit')
                            ->required()
                            ->numeric()
                            ->inputMode('decimal'),

                    ])
                    ->collapsible(false)
                    ->defaultItems(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')->searchable()->sortable(),
                TextColumn::make('date')->searchable()->sortable(),
                TextColumn::make('remark')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListJournals::route('/'),
            'create' => Pages\CreateJournal::route('/create'),
            'edit' => Pages\EditJournal::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
