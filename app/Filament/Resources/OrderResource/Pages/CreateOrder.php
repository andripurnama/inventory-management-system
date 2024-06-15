<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Account;
use App\Models\Item;
use App\Models\Journal;
use App\Models\Order;
use Carbon\Carbon;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Order has been created successfully';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::debug('form data', $data);
        DB::transaction(function () use ($data) {
            $order = Order::create([
                'grand_total' => $data['grand_total'],
                'customer_id' => $data['customer_id'],
                'status' => $data['status']
            ]);

            foreach ($data['order_items'] as $orderItem) {
                $item = Item::find($orderItem['item_id']);

                // Decrement the stock
                if ($item->stock >= $orderItem['quantity']) {
                    $item->stock -= $orderItem['quantity'];
                    $item->save();

                    $order->orderItems()->create([
                        'item_id' => $orderItem['item_id'],
                        'quantity' => $orderItem['quantity'],
                        'price' => $orderItem['price'],
                    ]);

                    $accountDebetId = Account::where('name', 'Persediaan Barang Jadi')->first()->id;
                    $journal = Journal::create(['remarks' => $order->order_reference, 'date' => Carbon::now()]);
                    $journal->journalDetails()->create([
                        'account_id' => $accountDebetId,
                        'reference' => $order->order_reference,
                        'reference_type' => 'order',
                        'debit' => $orderItem['price'],
                        'remarks' => 'Persediaan Barang Jadi' . $item->name . ' dengan order no ' . $order->order_reference
                    ]);
                    $accountCreditId = Account::where('name', 'Hutang Belum Ditagihkan')->first()->id;
                    $journal->journalDetails()->create([
                        'account_id' => $accountCreditId,
                        'reference' => $order->order_reference,
                        'reference_type' => 'order',
                        'credit' => $orderItem['price'],
                        'remarks' => 'Hutang Belum ditagihkan untuk' . $item->name . ' dengan order no ' . $order->order_reference
                    ]);
                } else {
                    throw new Exception("Not enough stock for item: " . $item->name);
                }
            }
        });
        return $data;
    }
}
