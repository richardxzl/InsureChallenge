<?php

namespace App\Services;

use SimpleXMLElement;
use App\DTOs\GenerateInsuranceXMLDTO;

final class GenerateInsuranceXmlService
{
    private GenerateInsuranceXMLDTO $generateInsuranceXMLDTO;
    private SimpleXMLElement $dataXmlElement;

    public function __invoke(GenerateInsuranceXMLDTO $generateInsuranceXMLDTO): string
    {
        $this->generateInsuranceXMLDTO = $generateInsuranceXMLDTO;

        $xml = new SimpleXMLElement('<TarificacionThirdPartyRequest/>');
        $xml->addChild('Cotizacion', $this->generateInsuranceXMLDTO->data['Cotizacion']);
        $data = $xml->addChild('Datos');
        $this->dataXmlElement = $data;

        $this->addInsuranceCompanyData();
        $this->addCoverageData();
        $this->addDriverData();
        $this->addGeneralData();
        $this->addOwnerData();
        $this->addPolicyHolderData();
        $this->addVehicleData();

        $xml->addChild('Empresa', $this->generateInsuranceXMLDTO->data['Empresa']);
        $xml->addChild('FaseTarificacion', $this->generateInsuranceXMLDTO->data['FaseTarificacion']);
        $xml->addChild('Identificador', $this->generateInsuranceXMLDTO->data['Identificador']);
        $xml->addChild('Plataforma', $this->generateInsuranceXMLDTO->data['Plataforma']);
        $xml->addChild('TipoTarificacion', $this->generateInsuranceXMLDTO->data['TipoTarificacion']);
        $xml->addChild('VersionCotizacion', $this->generateInsuranceXMLDTO->data['VersionCotizacion']);

        $this->addComparatorData($xml);

        return $xml->asXML();
    }

    private function addInsuranceCompanyData(): void
    {
        $insuranceCompanyData = $this->dataXmlElement->addChild('DatosAseguradora');

        foreach ($this->generateInsuranceXMLDTO->data['DatosAseguradora'] as $key => $value) {
            $this->addChildWithNil($insuranceCompanyData, $key, $value);
        }
    }

    private function addCoverageData(): void
    {
        $coverageData = $this->dataXmlElement->addChild('DatosCoberturas');

        foreach ($this->generateInsuranceXMLDTO->data['DatosCoberturas'] as $coverage) {
            $coverageDataChild = $coverageData->addChild('DatosCoberturas');

            foreach ($coverage as $key => $value) {
                $this->addChildWithNil($coverageDataChild, $key, $value);
            }
        }
    }

    private function addDriverData(): void
    {
        $driverData = $this->dataXmlElement->addChild('DatosConductor');

        foreach ($this->generateInsuranceXMLDTO->data['DatosConductor'] as $key => $value) {
            $this->addChildWithNil($driverData, $key, $value);
        }
    }

    private function addGeneralData(): void
    {
        $generalData = $this->dataXmlElement->addChild('DatosGenerales');

        foreach ($this->generateInsuranceXMLDTO->data['DatosGenerales'] as $key => $value) {
            $this->addChildWithNil($generalData, $key, $value);
        }
    }

    private function addOwnerData(): void
    {
        $ownerData = $this->dataXmlElement->addChild('DatosPropietario');

        foreach ($this->generateInsuranceXMLDTO->data['DatosPropietario'] as $key => $ownerValue) {
            if (is_array($ownerValue)) {
                $ownerDataChild = $ownerData->addChild($key);
                foreach ($ownerValue as $index => $ownerDataValue) {
                    if (is_array($ownerDataValue)) {
                        $ownerDataHomeChild = $ownerDataChild->addChild($index);
                        foreach ($ownerDataValue as $subKey => $subValue) {
                            $this->addChildWithNil($ownerDataHomeChild, $subKey, $subValue);
                        }
                        continue;
                    }
                    $this->addChildWithNil($ownerDataChild, $index, $ownerDataValue);
                }
                continue;
            }

            $ownerData->addChild($key, $ownerValue);
        }
    }

    private function addPolicyHolderData(): void
    {
        $policyHolderData = $this->dataXmlElement->addChild('DatosTomador');

        foreach ($this->generateInsuranceXMLDTO->data['DatosTomador'] as $key => $ownerValue) {
            if (is_array($ownerValue)) {
                $ownerDataHomeChild = $policyHolderData->addChild($key);
                foreach ($ownerValue as $subKey => $subValue) {
                    $this->addChildWithNil($ownerDataHomeChild, $subKey, $subValue);
                }
                continue;
            }
            $this->addChildWithNil($policyHolderData, $key, $ownerValue);
        }
    }

    private function addVehicleData(): void
    {
        $vehicleData = $this->dataXmlElement->addChild('DatosVehiculo');

        foreach ($this->generateInsuranceXMLDTO->data['DatosVehiculo'] as $key => $value) {
            $this->addChildWithNil($vehicleData, $key, $value);
        }
    }

    private function addComparatorData(SimpleXMLElement $parent): void
    {
        $comparatorData = $parent->addChild('DatosComparadores');

        foreach ($this->generateInsuranceXMLDTO->data['DatosComparadores'] as $key => $value) {
            $this->addChildWithNil($comparatorData, $key, $value);
        }
    }

    private function addChildWithNil(SimpleXMLElement $parent, string $name, string|int|null $value): void
    {
        $child = $parent->addChild($name, $value ?? '');
        if (is_null($value) || $value === '') {
            $child->addAttribute('xsi:nil', 'true');
        }
    }
}
