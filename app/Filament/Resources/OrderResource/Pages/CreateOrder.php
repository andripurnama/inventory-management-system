<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Account;
use App\Models\Item;
use App\Models\Journal;
use App\Models\JournalDetail;
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

    protected function afterCreate(): void
    {
        $accountId = Account::where('code', '1162.00.00')->first()->code;
        // Runs after the form fields are saved to the database.
        DB::transaction(function () use ($accountId) {
            $journal = Journal::create([
                'remarks' => 'Sales Order Number : ' . $this->record->order_reference,
                'date' => Carbon::now()
            ]);
            $journalDetail = JournalDetail::create([
                'journal_id' => $journal->id,
                'account_id' => $accountId,
                'reference' => $this->record->order_reference,
                'reference_type' => 'Sales Order',
                'debit' => 0,
                'credit' => $this->record->grand_total
            ]);
            JournalDetail::create([
                'journal_id' => $journal->id,
                'account_id' => $accountId,
                'reference' => $this->record->order_reference,
                'reference_type' => 'Sales Order',
                'debit' => 0,
                'credit' => $this->record->grand_total
            ]);
        });
    }
}
