<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class InsuranceXmlApiTest extends TestCase
{
    public function testGenerateApiUploadsValidJsonAndReceivesXmlResponse()
    {
        Storage::fake('local');

        $data = [
            'car_brand' => 'Toyota',
            'holder' => 'CONDUCTOR_PRINCIPAL',
            'occasionalDriver' => 'NO',
            'reference_code' => '5555R5555',
        ];

        $jsonContent = json_encode($data);
        $file = UploadedFile::fake()->createWithContent('data.json', $jsonContent);

        $response = $this->postJson('/api/upload-json', [
            'jsonFile' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function testGenerateApiFailsIfUploadedFileIsNotJson()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->postJson('/api/upload-json', [
            'jsonFile' => $file,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Invalid file uploaded. Please upload a valid JSON file.',
        ]);

        $response->assertJsonStructure([
            'errors' => ['jsonFile']
        ]);
    }

    public function testGenerateApiFailsIfJsonHasInvalidSyntax()
    {
        Storage::fake('local');

        $invalidJson = '{ "key" "value" }'; // JSON mal formado
        $file = UploadedFile::fake()->createWithContent('data.json', $invalidJson);

        $response = $this->postJson('/api/upload-json', [
            'jsonFile' => $file,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => 'Failed to generate XML file.',
        ]);
    }

    public function testGenerateApiFailsIfMissingFields()
    {
        Storage::fake('local');

        $data = [
            'car_brand' => 'Toyota',
        ];

        $jsonContent = json_encode($data);
        $file = UploadedFile::fake()->createWithContent('data.json', $jsonContent);

        $response = $this->postJson('/api/upload-json', [
            'jsonFile' => $file,
        ]);

        $response->assertStatus(400);

        $response->assertJson([
            'message' => 'Failed to generate XML file.',
        ]);
    }


}
