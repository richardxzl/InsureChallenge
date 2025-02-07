<?php

namespace App\Validators;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class InsuranceJsonFileValidator
{
    /**
     * @throws ValidationException
     */
    public static function validate(Request $request): void
    {
        $request->validate([
            'jsonFile' => 'required|file|mimes:json|max:10240',
        ]);

        $file = $request->file('jsonFile');
        if ($file->getClientOriginalExtension() !== 'json') {
            throw ValidationException::withMessages([
                'jsonFile' => 'Invalid file type. Only JSON files are allowed.'
            ]);
        }
    }
}
