<?php

namespace App\Http\Controllers;

use App\Validators\InsuranceJsonFileValidator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\{Artisan, File};
use Illuminate\Http\{JsonResponse, Response, Request};

class InsuranceXmlController extends Controller
{
    public function uploadJson(Request $request): JsonResponse|Response
    {
        try {
            InsuranceJsonFileValidator::validate($request);
            // Get the JSON file from the request
            $file = $request->file('jsonFile');
            $jsonPath = $file->getPathname();

            // Call the Artisan command to generate the XML file
            Artisan::call('generate:insurance-xml', [
                'jsonPath' => $jsonPath,
            ]);

            $timestamp = date('Ymd_His');
            $xmlFileName = "insurance_{$timestamp}.xml";
            $xmlPath = storage_path($xmlFileName);

            // Check if the XML file was generated
            if (!File::exists($xmlPath)) {
                throw new \Exception("Failed to generate XML file.");
            }

            // Return the XML file as a response
            $xmlContent = File::get($xmlPath);
            unlink($xmlPath);

            return response($xmlContent, 200)
                ->header('Content-Type', 'application/xml');

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Invalid file uploaded. Please upload a valid JSON file.',
                'errors' => $e->errors()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
