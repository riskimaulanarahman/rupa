<?php

namespace App\Console\Commands;

use App\Services\BillingService;
use Illuminate\Console\Command;

class ProcessBilling extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rupa:process-billing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Proses status langganan tenant dan pembuatan invoice rutin';

    /**
     * Execute the console command.
     */
    public function handle(BillingService $billingService)
    {
        $this->info('Memulai pembaruan status langganan...');
        $billingService->updateSubscriptionStatuses();
        $this->info('Status langganan berhasil diperbarui.');

        $this->info('Memproses penagihan rutin...');
        $billingService->processBilling();
        $this->info('Proses penagihan selesai.');

        return Command::SUCCESS;
    }
}
