<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Services\InsuranceXmlService;

class GenerateInsuranceXML extends Command
{
    protected $signature = 'generate:insurance-xml {jsonPath}';
    protected $description = 'Generate an XML based on input JSON';

    public function __construct(
        private readonly InsuranceXmlService $insuranceXmlService
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $jsonPath = $this->argument('jsonPath');
        $timestamp = date('Ymd_His');
        $xmlFileName = "insurance_{$timestamp}.xml";
        $xmlPath = storage_path($xmlFileName);

        try {
            $xmlContent = $this->insuranceXmlService->processJsonFile($jsonPath);
            File::put($xmlPath, $xmlContent);
            $this->info("XML successfully generated: " . $xmlPath);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
