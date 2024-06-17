<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Models\Account;
use App\Models\Item;
use App\Models\Journal;
use App\Models\JournalDetail;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Purchase Order has been created successfully';
    }
    protected function afterCreate(): void
    {
        $purchaseOrderDetail = $this->record->purchaseItems;
        DB::transaction(function () use ($purchaseOrderDetail) {

            $this->incrementStock($purchaseOrderDetail);
            $this->createJournal();
        });
    }

    private function createJournal(): void
    {
        $accountPersediaanBarangId = Account::where('name', 'Persediaan Barang Jadi')->first()->id;
        $accountHutangBelumDitagihkanId = Account::where('name', 'Hutang Belum Ditagihkan')->first()->id;
        $journal = Journal::create([
            'remarks' => 'Purchase Order Number : ' . $this->record->purchase_reference,
            'date' => Carbon::now()
        ]);
        JournalDetail::create([
            'journal_id' => $journal->id,
            'account_id' => $accountPersediaanBarangId,
            'reference' => $this->record->purchase_reference,
            'reference_type' => 'Purchase Order',
            'debit' => $this->record->grand_total,
            'credit' => 0
        ]);
        JournalDetail::create([
            'journal_id' => $journal->id,
            'account_id' => $accountHutangBelumDitagihkanId,
            'reference' => $this->record->purchase_reference,
            'reference_type' => 'Purchase Order',
            'debit' => 0,
            'credit' => $this->record->grand_total
        ]);
    }

    private function incrementStock($purchaseOrderDetail): void
    {
        foreach ($purchaseOrderDetail as $key => $value) {
            $item = Item::whereId($value->item_id)->first();
            $item->stock += $value->quantity;
            $item->save();
        }
    }
}
