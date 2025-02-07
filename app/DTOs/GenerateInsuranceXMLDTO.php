<?php

namespace App\DTOs;

use Carbon\Carbon;

readonly class GenerateInsuranceXMLDTO
{
    public array $data;

    public function __construct(array $inputData)
    {
        $previousInsuranceYears = 0;
        if (!empty($inputData['prevInsurance_contractDate'])) {
            $contractYear = Carbon::parse($inputData['prevInsurance_contractDate'])->year;
            $currentYear = now()->year;
            $previousInsuranceYears = max(0, $currentYear - $contractYear);
        }

        $isMainDriverPolicyholder = ($inputData['holder'] ?? null) === 'CONDUCTOR_PRINCIPAL' ? 'S' : 'N';
        $isSingleDriver = ($inputData['occasionalDriver'] ?? null) === 'SI' ? 'N' : 'S';
        $occasionalDriverCount = ($inputData['occasionalDriver'] ?? null) === 'S' ? 1 : 0;

        $this->data = [
            'Cotizacion' => '0',
            'DatosAseguradora' => [
                'AniosCiaAnterior' => $inputData['prevInsurance_years'] ?? null,
                'AniosTitularSeguro' => $inputData['prevInsurance_years'] ?? null,
                'CiaAnterior' => $inputData['prevInsurance_company'] ?? null,
                'CincoDigPolAnterior' => null,
                'FecUltimoSiniestro' => null,
                'NroSiniestroCulpa' => $inputData['prevInsurance_claimsCount'] ?? null,
                'SeguroEnVigor' => $this->isInsuranceActive($inputData) ? 'S' : 'N', // Duda en el documento dice yes or no, pero veo el XML de ejemplo y es S o N
                'TiempoSinSeguro' => null,
            ],
            'DatosCoberturas' => [
                'DatosCoberturas' => [
                    'CodCobertura' => null,
                    'Valor' => null
                ]
            ],
            'DatosConductor' => [
                'CodDocumento' => $inputData['driver_idType'] ?? null,
                'CodPaisExpedicion' => $inputData['driver_licensePlace'] ?? null,
                'CodPaisNacimiento' => $inputData['driver_birthPlace'] ?? null,
                'CodPostal' => $inputData['use_postalCode'] ?? null,
                'EstadoCivil' => $inputData['driver_civilStatus'] ?? null,
                'FecCarnet' => $inputData['driver_licenseDate'] ?? null,
                'FecNacimiento' => $inputData['driver_birthDate'] ?? null,
                'HijosCarnet' => $inputData['driver_children'] ?? null,
                'Ocupacion' => $inputData['driver_profession'] ?? null,
                'Profesion' => $inputData['driver_profession'] ?? null,
                'Sexo' => $inputData['driver_sex'] ?? null,
                'SubDocum' => null,
            ],
            'DatosGenerales' => [
                'CodMedAncl' => null,
                'CondPpalEsTomador' => $isMainDriverPolicyholder,
                'ConductorUnico' => $isSingleDriver,
                'ContrataAlgunPack' => null,
                'Cotizacion' => null,
                'EntornoOrigen' => null,
                'FecCot' => now()->toDateString(),
                'FecCotAncl' => null,
                'AnosSegAnte' => $previousInsuranceYears,
                'FecUltimoSeguro' => null,
                'Franquicia' => null,
                'Idioma' => null,
                'ImporteFranquicia' => null,
                'MasVehiculos' => null,
                'McaPagoTarjeta' => null,
                'Mediador' => null,
                'Modalidad' => null,
                'MotivoBonus' => null,
                'MotivoEstado' => null,
                'NivelDP' => null,
                'NivelLU' => null,
                'NivelRC' => null,
                'NroCochesFamilia' => null,
                'NroCondOca' => $occasionalDriverCount,
                'NroOpera' => null,
                'NroRiesgo' => null,
                'PctOpera' => null,
                'Poliza' => null,
                'Polizaorigen' => null,
                'Polizatarificada' => null,
                'Ramo' => null,
                'ScoreA' => null,
                'ScoreB' => null,
                'SeguroEnVigor' => null,
                'SubMediador' => null,
                'Subramo' => null,
                'Suplemento' => null,
                'TallerConcertado' => null,
                'UsuarioCot' => null,
                'VersionCot' => null,
            ],
            'DatosPropietario' => [
                'Propietario' => [
                    'CodActividad' => null,
                    'CodDocumento' => $inputData['CodDocumento'] ?? null,
                    'CodError' => null,
                    'CodPais' => $inputData['driver_birthPlace'] ?? null,
                    'Domicilio' => [
                        'CodPostal' => $inputData['use_postalCode'] ?? null,
                    ],
                    'Empresa' => 4, // suponiendo que es ACME
                    'FecCarnet' => $inputData['driver_licenseDate'] ?? null,
                    'FecNacimiento' => $inputData['driver_birthDate'] ?? null,
                    'NroDocumento' => $inputData['driver_id'] ?? null,
                    'SubDocum' => null,
                ],
                'QuienEsPropietario' => 2,
                'TomadorEsPropietario' => 'S',
            ],
            'DatosTomador' => [
                'CodActividad' => null,
                'CodDocumento' => $inputData['CodDocumento'] ?? null,
                'CodError' => null,
                'CodPais' => $inputData['driver_birthPlace'] ?? null,
                'Domicilio' => [
                    'CodPostal' => $inputData['use_postalCode'] ?? null,
                ],
                'Empresa' => 4, // suponiendo que es ACME
                'FecCarnet' => $inputData['driver_licenseDate'] ?? null,
                'FecNacimiento' => $inputData['driver_birthDate'] ?? null,
                'NroDocumento' => $inputData['driver_id'] ?? null,
                'SubDocum' => null
            ],
            'DatosVehiculo' => [
                'CodMarca' => $inputData['car_brand'] ?? null,
                'CodModelo' => $inputData['car_model'] ?? null,
                'CodUso' => $inputData['use_carUse'] ?? null,
                'CodVersion' => $inputData['car_version'] ?? null,
                'KmVehiculo' => $inputData['use_kmsYear'] ?? null,
                'Parking' => $inputData['use_nightParking'] ?? null,
            ],
            'Empresa' => "4",
            'FaseTarificacion' => null,
            'Identificador' => $inputData['reference_code'] ?? null,
            'Plataforma' => null,
            'TipoTarificacion' => null,
            'VersionCotizacion' => '0',
            'DatosComparadores' => [
                'MorosidadComparador' => null,
                'AnioVdaActual' => null,
                'MultasUlt3anios' => null,
                'TipoSeguro' => null
            ]
        ];
    }

    private function isInsuranceActive(array $data): bool
    {
        return (
            ($data['prevInsurance_exists'] ?? 'NO') === 'SI'
            && !empty($data['prevInsurance_expirationDate'])
            && Carbon::parse($data['prevInsurance_expirationDate'])->isFuture()
        );
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}

