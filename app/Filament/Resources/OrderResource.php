<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Models\Customer;
use App\Models\Item;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-m-shopping-cart';

    protected static ?string $navigationGroup = 'Transaction';

    protected static ?string $navigationLabel = 'Sales Order';

    protected ?bool $hasDatabaseTransactions = true;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('customer_id')
                    ->label('Customer Name')
                    ->options(Customer::all()->pluck('name', 'id'))
                    ->searchable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'Unpaid' => 'Unpaid',
                        'Paid' => 'Paid'
                    ]),
                Repeater::make('orderItems')
                    ->relationship()
                    ->schema([
                        Forms\Components\Select::make('item_id')
                            ->relationship('item', 'name')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $item = Item::find($state);
                                if ($item) {
                                    $set('price', $item->sale_price);
                                    $quantity = $get('quantity') ?: 0;
                                    $set('total', $quantity * $item->sale_price);
                                }
                                static::recalculateGrandTotal($set, $get);
                            }),
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
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->step(0.01),
                        Forms\Components\TextInput::make('total')
                            ->readOnly()
                            ->numeric()
                            ->step(1)
                            ->reactive()
                    ])
                    ->columnSpanFull()
                    ->columns(3)
                    ->addActionLabel('Add Order Item')
                    ->afterStateUpdated(function (callable $set, callable $get) {
                        static::recalculateGrandTotal($set, $get);
                    }),
                TextInput::make('grand_total')->readOnly()->numeric()->default(0),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_reference')->searchable()->sortable(),
                TextColumn::make('customer.name')->searchable()->sortable(),
                TextColumn::make('status')->badge(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScope(SoftDeletingScope::class);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'customer.name'];
    }

    protected static function recalculateGrandTotal(callable $set, callable $get)
    {
        $orderItems = $get('orderItems') ?? [];
        $grandTotal = array_reduce($orderItems, function ($total, $item) {
            return $total + ($item['quantity'] * $item['price']);
        }, 0);
        $set('grand_total', $grandTotal);
    }
}
