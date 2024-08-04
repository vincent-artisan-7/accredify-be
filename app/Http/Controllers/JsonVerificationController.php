<?php

namespace App\Http\Controllers;

use App\DTO\VerificationResult;
use App\Services\JsonVerificationService;
use Illuminate\Http\Request;

class JsonVerificationController extends Controller
{
    private JsonVerificationService $jsonVerificationService;

    public function __construct(JsonVerificationService $jsonVerificationService)
    {
        $this->jsonVerificationService = $jsonVerificationService;
    }

    public function verify(Request $request): VerificationResult
    {
        $request->validate([
            'file' => 'required|file|mimes:json',
        ]);

        $file = $request->file('file');
        $content = json_decode(file_get_contents($file->getRealPath()), true);

        return $this->jsonVerificationService->verifyJson($content);
    }
}
