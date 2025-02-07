<?php

namespace App\Services;

use Carbon\Carbon;
use SimpleXMLElement;
use App\DTOs\InsuranceRequestDTO;
use Illuminate\Support\Facades\File;
use App\Validators\InsuranceRequestValidator;

class InsuranceXmlService
{

    public function processJsonFile(string $jsonPath): string
    {
        if (!File::exists($jsonPath)) {
            throw new \Exception("The file at path $jsonPath does not exist.");
        }

        $jsonData = json_decode(file_get_contents($jsonPath), true);

        if (!is_array($jsonData)) {
            throw new \Exception('Error reading JSON: ' . json_last_error_msg());
        }

        $validatedData = InsuranceRequestValidator::validate($jsonData);
        $dto = InsuranceRequestDTO::fromArray($validatedData);
        return $this->generateXml($dto);
    }

    public function generateXml(InsuranceRequestDTO $insuranceRequestDTO): string
    {
        $xml = new SimpleXMLElement('<TarificacionThirdPartyRequest/>');
        $xml->addChild('Cotizacion', '0');

        $data = $xml->addChild('Datos');
        $this->addInsuranceCompanyData($data, $insuranceRequestDTO);
        $this->addCoverageData($data, $insuranceRequestDTO);
        $this->addDriverData($data, $insuranceRequestDTO);
        $this->addGeneralData($data, $insuranceRequestDTO);
        $this->addOwnerData($data, $insuranceRequestDTO);
        $this->addPolicyHolderData($data, $insuranceRequestDTO);
        $this->addVehicleData($data, $insuranceRequestDTO);

        $xml->addChild('Empresa', '4');
        $xml->addChild('FaseTarificacion');
        $xml->addChild('Identificador', $insuranceRequestDTO->empty);
        $xml->addChild('Plataforma', $insuranceRequestDTO->empty);
        $xml->addChild('TipoTarificacion', $insuranceRequestDTO->empty);
        $xml->addChild('VersionCotizacion', '0');

        $this->addComparatorData($xml, $insuranceRequestDTO);

        return $xml->asXML();
    }

    private function addInsuranceCompanyData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $insuranceCompanyData = $parent->addChild('DatosAseguradora');
        $this->addChildWithNil($insuranceCompanyData, 'AniosCiaAnterior', $insuranceRequestDTO->prevInsurance_years);
        $this->addChildWithNil($insuranceCompanyData, 'AniosTitularSeguro', $insuranceRequestDTO->prevInsurance_years);
        $this->addChildWithNil($insuranceCompanyData, 'CiaAnterior', $insuranceRequestDTO->prevInsurance_company);
        $this->addChildWithNil($insuranceCompanyData, 'CincoDigPolAnterior', $insuranceRequestDTO->empty);
        $this->addChildWithNil($insuranceCompanyData, 'FecUltimoSiniestro', $insuranceRequestDTO->empty);
        $this->addChildWithNil($insuranceCompanyData, 'NroSiniestroCulpa', $insuranceRequestDTO->empty);

        $insuranceActive = (
            $insuranceRequestDTO->prevInsurance_exists === 'SI'
            && !empty($insuranceRequestDTO->prevInsurance_expirationDate)
            && Carbon::parse($insuranceRequestDTO->prevInsurance_expirationDate)->isFuture()
        ) ? 'YES' : 'NO';

        $this->addChildWithNil($insuranceCompanyData, 'SeguroEnVigor', $insuranceActive);
        $this->addChildWithNil($insuranceCompanyData, 'TiempoSinSeguro', $insuranceRequestDTO->empty);
    }

    private function addCoverageData(SimpleXMLElement $parent, InsuranceRequestDTO $dto): void
    {
        $coverageData = $parent->addChild('DatosCoberturas');
        $coverageDataChild = $coverageData->addChild('DatosCoberturas');
        $this->addChildWithNil($coverageDataChild, 'CodCobertura', $dto->empty);
        $this->addChildWithNil($coverageDataChild, 'Valor', $dto->empty);
    }

    private function addDriverData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $driverData = $parent->addChild('DatosConductor');
        $this->addChildWithNil($driverData, 'CodDocumento', strtoupper($insuranceRequestDTO->driver_idType));
        $this->addChildWithNil($driverData, 'CodPaisExpedicion', $insuranceRequestDTO->driver_licensePlace);
        $this->addChildWithNil($driverData, 'CodPaisNacimiento', $insuranceRequestDTO->driver_birthPlace);
        $this->addChildWithNil($driverData, 'CodPostal', $insuranceRequestDTO->use_postalCode);
        $this->addChildWithNil($driverData, 'EstadoCivil', substr($insuranceRequestDTO->driver_civilStatus, 0, 1));
        $this->addChildWithNil($driverData, 'FecCarnet', $insuranceRequestDTO->driver_licenseDate);
        $this->addChildWithNil($driverData, 'FecNacimiento', $insuranceRequestDTO->driver_birthDate);
        $this->addChildWithNil($driverData, 'HijosCarnet', $insuranceRequestDTO->driver_children === 'SI' ? 'S' : 'N');
        $this->addChildWithNil($driverData, 'Ocupacion', $insuranceRequestDTO->driver_profession);
        $this->addChildWithNil($driverData, 'Profesion', $insuranceRequestDTO->driver_profession);
        $this->addChildWithNil($driverData, 'PuntosCarnet', $insuranceRequestDTO->empty);
        $this->addChildWithNil($driverData, 'Sexo', substr($insuranceRequestDTO->driver_sex, 0, 1));
        $this->addChildWithNil($driverData, 'SubDocum', $insuranceRequestDTO->empty);
    }

    private function addGeneralData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $generalData = $parent->addChild('DatosGenerales');
        $this->addChildWithNil($generalData, 'CodMedAncl', $insuranceRequestDTO->empty);

        $isMainDriverPolicyholder = ($insuranceRequestDTO->holder === 'CONDUCTOR_PRINCIPAL') ? 'YES' : 'NO';
        $this->addChildWithNil($generalData, 'CondPpalEsTomador', $isMainDriverPolicyholder);

        $isSingleDriver = ($insuranceRequestDTO->occasionalDriver === 'SI') ? 'NO' : 'YES';
        $this->addChildWithNil($generalData, 'ConductorUnico', $isSingleDriver);

        $this->addChildWithNil($generalData, 'ContrataAlgunPack', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Cotizacion', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'EntornoOrigen', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'FecCot', now()->toDateString());
        $this->addChildWithNil($generalData, 'FecCotAncl', $insuranceRequestDTO->empty);

        $previousInsuranceYears = 0;
        if (!empty($insuranceRequestDTO->prevInsurance_contractDate)) {
            $contractYear = Carbon::parse($insuranceRequestDTO->prevInsurance_contractDate)->year;
            $currentYear = now()->year;
            $previousInsuranceYears = max(0, $currentYear - $contractYear);
        }
        $this->addChildWithNil($generalData, 'AnosSegAnte', $previousInsuranceYears);

        $this->addChildWithNil($generalData, 'FecUltimoSeguro', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Franquicia', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Idioma', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'ImporteFranquicia', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'MasVehiculos', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'McaPagoTarjeta', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Mediador', $insuranceRequestDTO->empty);
        $generalData->addChild( 'Modalidad', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'MotivoBonus', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'MotivoEstado', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'NivelDP', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'NivelLU', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'NivelRC', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'NroCochesFamilia', $insuranceRequestDTO->empty);

        $occasionalDriverCount = ($insuranceRequestDTO->occasionalDriver === 'SI') ? 1 : 0;
        $this->addChildWithNil($generalData, 'NroCondOca', $occasionalDriverCount);

        $this->addChildWithNil($generalData, 'NroOpera', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'NroRiesgo', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'PctOpera', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Poliza', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Polizaorigen', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Polizatarificada', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Ramo', $insuranceRequestDTO->empty);
        $generalData->addChild( 'ScoreA', $insuranceRequestDTO->empty);
        $generalData->addChild( 'ScoreB', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'SeguroEnVigor', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'SubMediador', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Subramo', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'Suplemento', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'TallerConcertado', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'UsuarioCot', $insuranceRequestDTO->empty);
        $this->addChildWithNil($generalData, 'VersionCot', $insuranceRequestDTO->empty);
    }

    private function addOwnerData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $ownerData = $parent->addChild('DatosPropietario');
        $ownerDataChild = $ownerData->addChild('Propietario');
        $this->addChildWithNil($ownerDataChild, 'CodActividad', $insuranceRequestDTO->empty);
        $this->addChildWithNil($ownerDataChild, 'CodDocumento', $insuranceRequestDTO->driver_idType);
        $this->addChildWithNil($ownerDataChild, 'CodError', $insuranceRequestDTO->empty);
        $this->addChildWithNil($ownerDataChild, 'CodPais', $insuranceRequestDTO->driver_birthPlace);
        $ownerAddress = $ownerDataChild->addChild('Domicilio');
        $this->addChildWithNil($ownerAddress, 'CodPostal', $insuranceRequestDTO->use_postalCode);
        $ownerDataChild->addChild('Empresa', '4');
        $this->addChildWithNil($ownerDataChild, 'FecCarnet', $insuranceRequestDTO->driver_licenseDate);
        $this->addChildWithNil($ownerDataChild, 'FecNacimiento', $insuranceRequestDTO->driver_birthDate);
        $this->addChildWithNil($ownerDataChild, 'NroDocumento', $insuranceRequestDTO->driver_id);
        $this->addChildWithNil($ownerDataChild, 'SubDocum', $insuranceRequestDTO->empty);
        $ownerData->addChild('QuienEsPropietario', $insuranceRequestDTO->empty);
        $ownerData->addChild('TomadorEsPropietario', $insuranceRequestDTO->empty);
    }

    private function addPolicyHolderData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $policyholderData = $parent->addChild('DatosTomador');
        $this->addChildWithNil($policyholderData, 'CodActividad', $insuranceRequestDTO->empty);
        $this->addChildWithNil($policyholderData, 'CodDocumento', $insuranceRequestDTO->driver_idType);
        $this->addChildWithNil($policyholderData, 'CodError', $insuranceRequestDTO->empty);
        $this->addChildWithNil($policyholderData, 'CodPais', $insuranceRequestDTO->driver_birthPlace);
        $policyholderAddress = $policyholderData->addChild('Domicilio');
        $this->addChildWithNil($policyholderAddress, 'CodPostal', $insuranceRequestDTO->use_postalCode);
        $policyholderData->addChild('Empresa', '4');
        $this->addChildWithNil($policyholderData, 'FecCarnet', $insuranceRequestDTO->driver_licenseDate);
        $this->addChildWithNil($policyholderData, 'FecNacimiento', $insuranceRequestDTO->driver_birthDate);
        $this->addChildWithNil($policyholderData, 'NroDocumento', $insuranceRequestDTO->driver_id);
        $this->addChildWithNil($policyholderData, 'SubDocum', $insuranceRequestDTO->empty);
    }

    private function addVehicleData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $vehicleData = $parent->addChild('DatosVehiculo');
        $this->addChildWithNil($vehicleData, 'CodMarca', $insuranceRequestDTO->car_brand);
        $this->addChildWithNil($vehicleData, 'CodModelo', $insuranceRequestDTO->car_model);
        $this->addChildWithNil($vehicleData, 'CodTiempoCompra', $insuranceRequestDTO->empty);
        $this->addChildWithNil($vehicleData, 'CodUso', $insuranceRequestDTO->use_carUse);
        $this->addChildWithNil($vehicleData, 'CodVersion', $insuranceRequestDTO->car_version);
        $this->addChildWithNil($vehicleData, 'FecMatriculacion', $insuranceRequestDTO->car_registrationDate);
        $this->addChildWithNil($vehicleData, 'KmVehiculo', $insuranceRequestDTO->use_kmsYear);
        $this->addChildWithNil($vehicleData, 'Parking', $insuranceRequestDTO->use_nightParking);
        $this->addChildWithNil($vehicleData, 'ValorVehiculo', $insuranceRequestDTO->empty);
    }

    private function addComparatorData(SimpleXMLElement $parent, InsuranceRequestDTO $insuranceRequestDTO): void
    {
        $comparatorData = $parent->addChild('DatosComparadores');
        $this->addChildWithNil($comparatorData, 'MorosidadComparador', $insuranceRequestDTO->empty);
        $this->addChildWithNil($comparatorData, 'AnioVdaActual', $insuranceRequestDTO->empty);
        $this->addChildWithNil($comparatorData, 'MultasUlt3anios', $insuranceRequestDTO->empty);
        $this->addChildWithNil($comparatorData, 'TipoSeguro', $insuranceRequestDTO->empty);
    }

    private function addChildWithNil(SimpleXMLElement $parent, string $name, string|int|null $value): void
    {
        $child = $parent->addChild($name, $value ?? '');
        if (is_null($value) || $value === '') {
            $child->addAttribute('xsi:nil', 'true');
        }
    }
}
