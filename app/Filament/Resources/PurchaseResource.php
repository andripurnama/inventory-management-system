<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-m-document-text';

    protected static ?string $navigationGroup = 'Transaction';

    protected ?bool $hasDatabaseTransactions = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('supplier_id')
                    ->label('Supplier Name')
                    ->options(Supplier::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Unpaid' => 'Unpaid',
                        'Paid' => 'Paid'
                    ]),
                Textarea::make('remarks')->columnSpanFull(),
                TableRepeater::make('purchaseItems')
                    ->relationship()
                    ->label('Purchase Item Lists')
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'name')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $item = Item::find($state);
                                if ($item) {
                                    $set('price', $item->purchase_price);
                                    $quantity = $get('quantity') ?: 0;
                                    $set('total', $quantity * $item->purchase_price);
                                }
                                static::recalculateGrandTotal($set, $get);
                            }),
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->readOnly(),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $quantity = $get('quantity');
                                $price = $get('price');
                                $total = $quantity * $price;
                                $set('total', $total);
                                static::recalculateGrandTotal($set, $get);
                            }),
                        Forms\Components\TextInput::make('total')
                            ->readOnly()
                            ->reactive()
                    ])
                    ->collapsible(false)
                    ->defaultItems(3)
                    ->colStyles([
                        'item_id' => 'width: 300px;',
                    ])
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        static::recalculateGrandTotal($set, $get);
                    }),
                TextInput::make('grand_total')->readOnly()->default(0)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('purchase_reference')->sortable()->searchable(),
                TextColumn::make('supplier.name')->sortable()->searchable(),
                TextColumn::make('grand_total')->sortable()->searchable(),
                TextColumn::make('status')->sortable()->searchable(),
                TextColumn::make('remarks')->sortable()->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ForceDeleteAction::make()
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    protected static function recalculateGrandTotal(callable $set, callable $get)
    {
        $purchaseItems = $get('purchaseItems') ?? [];
        $grandTotal = array_reduce($purchaseItems, function ($total, $item) {
            return $total + ($item['quantity'] * $item['price']);
        }, 0);
        $set('grand_total', $grandTotal);
    }
}
