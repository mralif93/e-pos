<?php

namespace App\Domains\Sales\Listeners;

use App\Domains\Sales\Events\SaleCompleted;
use App\Services\LHDNService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SubmitToLHDNEInvoice implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;

        // E-Invoicing logic (LHDN MyInvois)
        try {
            $lhdnService = new LHDNService();
            $lhdnService->submitSale($sale);
            
            Log::info("E-Invoice submission triggered for Sale #{$sale->id}");
        } catch (\Exception $e) {
            Log::error("Async E-Invoice Submission Error for Sale #{$sale->id}: " . $e->getMessage());
            
            // Allow Queue to retry
            throw $e;
        }
    }
}
