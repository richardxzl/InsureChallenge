<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\DTOs\GenerateInsuranceXMLDTO;
use App\Validators\InsuranceRequestValidator;
use App\Services\GenerateInsuranceXmlService;

final class GenerateInsuranceXMLCommand extends Command
{
    protected $signature = 'generate:insurance-xml {jsonPath}';
    protected $description = 'Generate an XML based on input JSON';

    public function __construct(
        private readonly GenerateInsuranceXmlService $generateInsuranceXmlService
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

            if (!File::exists($jsonPath)) {
                throw new \Exception("The file at path $jsonPath does not exist.");
            }

            $jsonData = json_decode(file_get_contents($jsonPath), true);

            if (!is_array($jsonData)) {
                throw new \Exception('Error reading JSON: ' . json_last_error_msg());
            }

            $validatedData = InsuranceRequestValidator::validate($jsonData);
            $dto = GenerateInsuranceXMLDTO::fromArray($validatedData);

            $xmlContent = $this->generateInsuranceXmlService->__invoke($dto);

            File::put($xmlPath, $xmlContent);
            $this->info("XML successfully generated: " . $xmlPath);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return 1;
        }

        return 0;
    }
}
