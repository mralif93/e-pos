<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\EInvoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class LHDNService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiUrl;
    protected string $mode;

    public function __construct()
    {
        $this->clientId = config('services.lhdn.client_id', '');
        $this->clientSecret = config('services.lhdn.client_secret', '');
        $this->mode = config('services.lhdn.mode', 'mock'); // 'mock', 'sandbox', 'production'
        
        $this->apiUrl = $this->mode === 'production' 
            ? 'https://api.myinvois.hasil.gov.my/api/v1.0' 
            : 'https://preprod-api.myinvois.hasil.gov.my/api/v1.0';
    }

    /**
     * Submit a sale to LHDN MyInvois.
     */
    public function submitSale(Sale $sale): EInvoice
    {
        if ($this->mode === 'mock') {
            return $this->mockSubmission($sale);
        }

        try {
            $accessToken = $this->getAccessToken();
            $xml = $this->generateUblXml($sale);
            
            // 1. Save XML for record-keeping
            $xmlPath = "invoices/xml/{$sale->id}_" . time() . ".xml";
            Storage::disk('private')->put($xmlPath, $xml);

            // 2. Submit to LHDN
            $response = Http::withToken($accessToken)
                ->post("{$this->apiUrl}/documentsubmissions", [
                    'documents' => [
                        [
                            'format' => 'XML',
                            'document' => base64_encode($xml),
                            'documentHash' => hash('sha256', $xml),
                            'codeNumber' => $sale->uuid,
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $submissionId = $data['submissionId'] ?? null;
                $documentId = $data['acceptedDocuments'][0]['uuid'] ?? null;

                return EInvoice::create([
                    'sale_id' => $sale->id,
                    'lhdn_invoice_id' => $documentId,
                    'status' => 'submitted',
                    'xml_path' => $xmlPath,
                ]);
            }

            throw new Exception("LHDN Submission Failed: " . $response->body());

        } catch (Exception $e) {
            Log::error("E-Invoice Submission Error for Sale #{$sale->id}: " . $e->getMessage());
            
            return EInvoice::create([
                'sale_id' => $sale->id,
                'status' => 'failed',
                'rejection_reason' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get OAuth2 Access Token from LHDN Identity Server.
     */
    protected function getAccessToken(): string
    {
        $idpUrl = $this->mode === 'production'
            ? 'https://idp.myinvois.hasil.gov.my/connect/token'
            : 'https://preprod-idp.myinvois.hasil.gov.my/connect/token';

        $response = Http::asForm()->post($idpUrl, [
            'grant_type' => 'client_credentials',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'scope' => 'InvoicingAPI',
        ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new Exception("LHDN Authentication Failed.");
    }

    /**
     * Generate UBL 2.1 XML for LHDN (Simplified for this roadmap).
     */
    protected function generateUblXml(Sale $sale): string
    {
        // This would typically use a library like 'numomaduro/laravel-ubl' 
        // or a custom XML builder according to LHDN SDK.
        return "<?xml version="1.0" encoding="UTF-8"?><Invoice>...</Invoice>";
    }

    /**
     * Mock submission for development/testing.
     */
    protected function mockSubmission(Sale $sale): EInvoice
    {
        Log::info("MOCK: Submitting Sale #{$sale->id} to LHDN MyInvois.");
        
        return EInvoice::create([
            'sale_id' => $sale->id,
            'lhdn_invoice_id' => 'LHDN-' . strtoupper(str_replace('-', '', $sale->uuid)),
            'status' => 'cleared',
            'qr_code_path' => 'mock-qr-code.png',
        ]);
    }
}
