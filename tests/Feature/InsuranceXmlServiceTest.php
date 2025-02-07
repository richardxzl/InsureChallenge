<?php

namespace Tests\Feature;

use Tests\TestCase;
use SimpleXMLElement;
use App\DTOs\InsuranceRequestDTO;
use Illuminate\Support\Facades\File;
use App\Services\InsuranceXmlService;
use Illuminate\Support\Facades\Storage;
use App\Validators\InsuranceRequestValidator;
use Illuminate\Validation\ValidationException;

class InsuranceXmlServiceTest extends TestCase
{
    private InsuranceXmlService $insuranceXmlService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->insuranceXmlService = new InsuranceXmlService();
    }

    private function getValidDto(): InsuranceRequestDTO
    {
        return new InsuranceRequestDTO(
            car_brand: 'Toyota',
            car_fuel: 'Gasolina',
            car_model: 'Corolla',
            car_power: '132',
            car_purchaseDate: '2015-01-01',
            car_purchaseSituation: 'Nuevo',
            car_registrationDate: '2015-01-01',
            car_version: '1.8',
            customer_journey_ok: 1,
            driver_birthDate: '1980-01-01',
            driver_birthPlace: 'ES',
            driver_birthPlaceMain: 'ES',
            driver_children: 'NO',
            driver_civilStatus: 'SOLTERO',
            driver_id: '12345678X',
            driver_idType: 'DNI',
            driver_licenseDate: '2010-01-01',
            driver_licensePlace: 'ES',
            driver_licensePlaceMain: 'ES',
            driver_profession: 'Profesor',
            driver_sex: 'M',
            holder: 'CONDUCTOR_PRINCIPAL',
            holder_birthDate: '1980-01-01',
            holder_civilStatus: 'SOLTERO',
            holder_id: '12345678X',
            holder_idType: 'DNI',
            holder_licenseDate: '2010-01-01',
            holder_profession: 'Profesor',
            holder_sex: 'MUJER',
            occasionalDriver: 'NO',
            occasionalDriver_birthDate: '1990-01-01',
            occasionalDriver_civilStatus: 'CASADO',
            occasionalDriver_id: '87654321X',
            occasionalDriver_idType: 'DNI',
            occasionalDriver_licenseDate: '2012-01-01',
            occasionalDriver_profession: 'Estudiante',
            occasionalDriver_sex: 'MUJER',
            occasionalDriver_youngest: 'NO',
            prevInsurance_claims: 'NO',
            prevInsurance_claimsCount: 0,
            prevInsurance_company: 'Sura',
            prevInsurance_companyYear: 5,
            prevInsurance_contractDate: '2015-01-01',
            prevInsurance_email: 'test@example.com',
            prevInsurance_emailRequest: 'NO',
            prevInsurance_exists: 'SI',
            prevInsurance_expirationDate: '2024-12-31',
            prevInsurance_fineAlcohol: 'NO',
            prevInsurance_fineOther: 'NO',
            prevInsurance_fineParking: 'NO',
            prevInsurance_fineSpeed: 'NO',
            prevInsurance_fines: 'NO',
            prevInsurance_modality: 'Full',
            prevInsurance_years: 5,
            reference_code: 'REF123',
            use_carUse: 'Private',
            use_kmsYear: 15000,
            use_nightParking: 'Garage',
            use_postalCode: '28001',
            empty: ''
        );
    }

    public function testGenerateXmlWithValidData()
    {
        $dto = $this->getValidDto();

        $xmlString = $this->insuranceXmlService->generateXml($dto);
        $xml = new SimpleXMLElement($xmlString);

        $this->assertContains((string) $xml->Datos->DatosAseguradora->SeguroEnVigor, ['YES', 'NO']);
        $this->assertContains((string) $xml->Datos->DatosGenerales->CondPpalEsTomador, ['YES', 'NO']);
        $this->assertContains((string) $xml->Datos->DatosGenerales->ConductorUnico, ['YES', 'NO']);
        $this->assertEquals(date('Y-m-d'), (string) $xml->Datos->DatosGenerales->FecCot);
        $this->assertIsNumeric((string) $xml->Datos->DatosGenerales->AnosSegAnte);
        $this->assertIsNumeric((string) $xml->Datos->DatosGenerales->NroCondOca);
    }

    public function testGenerateInsuranceXmlCommandSuccess()
    {
        $data = [
            'car_brand' => 'Toyota',
            'holder' => 'CONDUCTOR_PRINCIPAL',
            'occasionalDriver' => 'NO',
            'reference_code' => '2222R2222',
        ];

        $dto = InsuranceRequestDTO::fromArray($data);

        $jsonPath = storage_path('app/public/data/dataTest.json');
        $jsonContent = json_encode($dto);

        File::put($jsonPath, $jsonContent);

        $this->artisan('generate:insurance-xml', ['jsonPath' => $jsonPath])
            ->assertExitCode(0);

        unlink($jsonPath);
    }

    public function testGenerateInsuranceXmlCommandFailure()
    {
        $this->artisan('generate:insurance-xml', ['jsonPath' => 'non_existing_path.json'])
            ->assertExitCode(1);
    }

    public function testGenerateInsuranceXmlCommandInvalidJson()
    {
        Storage::fake('public');

        $invalidJson = '{ "key" "value" }';

        Storage::put('data/dataWithInvalidFormat.json', $invalidJson);

        $this->artisan('generate:insurance-xml', [
            'jsonPath' => storage_path('app/public/data/dataWithInvalidFormat.json')
        ])->assertExitCode(1);
    }

    public function testGenerateInsuranceXmlWithMissingFields()
    {
        $data = [
            'car_brand' => 'Toyota',
            'car_fuel' => 'Gasolina',
        ];

        try {
            InsuranceRequestValidator::validate($data);
        } catch (ValidationException $e) {
            $this->assertTrue(true, 'Validation failed as expected');

            $this->artisan('generate:insurance-xml', ['jsonPath' => 'path_to_invalid_json.json'])
                ->assertExitCode(1);
            return;
        }

        $this->fail('The validation did not fail as expected.');
    }
}
