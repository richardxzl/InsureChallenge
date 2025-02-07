<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class InsuranceRequestValidator
{
    /**
     * @throws ValidationException
     */
    public static function validate(array $data): array
    {
        $rules = [
            'car_brand' => 'nullable|string',
            'car_fuel' => 'nullable|string',
            'car_model' => 'nullable|string',
            'car_power' => 'nullable|string',
            'car_purchaseDate' => 'nullable|date',
            'car_purchaseSituation' => 'nullable|string',
            'car_registrationDate' => 'nullable|date',
            'car_version' => 'nullable|string',
            'customer_journey_ok' => 'nullable|integer',
            'driver_birthDate' => 'nullable|date',
            'driver_birthPlace' => 'nullable|string',
            'driver_birthPlaceMain' => 'nullable|string',
            'driver_children' => 'nullable|string|in:SI,NO',
            'driver_civilStatus' => 'nullable|string',
            'driver_id' => 'nullable|string',
            'driver_idType' => 'nullable|string',
            'driver_licenseDate' => 'nullable|date',
            'driver_licensePlace' => 'nullable|string',
            'driver_licensePlaceMain' => 'nullable|string',
            'driver_profession' => 'nullable|string',
            'driver_sex' => 'nullable|string|in:HOMBRE,MUJER',
            'holder' => 'required|string',
            'holder_birthDate' => 'nullable|date',
            'holder_civilStatus' => 'nullable|string',
            'holder_id' => 'nullable|string',
            'holder_idType' => 'nullable|string',
            'holder_licenseDate' => 'nullable|date',
            'holder_profession' => 'nullable|string',
            'holder_sex' => 'nullable|string',
            'occasionalDriver' => 'required|string|in:SI,NO',
            'occasionalDriver_birthDate' => 'nullable|date',
            'occasionalDriver_civilStatus' => 'nullable|string',
            'occasionalDriver_id' => 'nullable|string',
            'occasionalDriver_idType' => 'nullable|string',
            'occasionalDriver_licenseDate' => 'nullable|date',
            'occasionalDriver_profession' => 'nullable|string',
            'occasionalDriver_sex' => 'nullable|string',
            'occasionalDriver_youngest' => 'nullable|string',
            'prevInsurance_claims' => 'nullable|string|in:SI,NO',
            'prevInsurance_claimsCount' => 'nullable|integer|min:0',
            'prevInsurance_company' => 'nullable|string',
            'prevInsurance_companyYear' => 'nullable|integer|min:0',
            'prevInsurance_contractDate' => 'nullable|date',
            'prevInsurance_email' => 'nullable|email',
            'prevInsurance_emailRequest' => 'nullable|string|in:SI,NO',
            'prevInsurance_exists' => 'nullable|string|in:SI,NO',
            'prevInsurance_expirationDate' => 'nullable|date',
            'prevInsurance_fineAlcohol' => 'nullable|string',
            'prevInsurance_fineOther' => 'nullable|string',
            'prevInsurance_fineParking' => 'nullable|string',
            'prevInsurance_fineSpeed' => 'nullable|string',
            'prevInsurance_fines' => 'nullable|string|in:SI,NO',
            'prevInsurance_modality' => 'nullable|string',
            'prevInsurance_years' => 'nullable|integer|min:0',
            'reference_code' => 'required|string',
            'use_carUse' => 'nullable|string',
            'use_kmsYear' => 'nullable|integer|min:0',
            'use_nightParking' => 'nullable|string',
            'use_postalCode' => 'nullable|string',
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
