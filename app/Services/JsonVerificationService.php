<?php

namespace App\Services;

use App\DTO\VerificationResult;
use App\Models\Verification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JsonVerificationService
{
    public function verifyFile(Request $request): VerificationResult
    {
        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048',
        ]);

        // Store the file
        $file = $request->file('file');
        $fileType = $file->getClientOriginalExtension(); // Get file extension
        $content = file_get_contents($file->getPathname());
        $json = json_decode($content, true);

        if ($json === null) {
            return new VerificationResult('invalid_signature', '');
        }

        // Extract data from JSON
        $data = $json['data'] ?? [];
        $signature = $json['signature'] ?? [];

        // Condition 1: Validate data.recipient
        if (empty($data['recipient']['name']) || empty($data['recipient']['email'])) {
            return new VerificationResult('invalid_recipient', $data['issuer']['name'] ?? '');
        }

        // Condition 2: Validate data.issuer
        if (empty($data['issuer']['name']) || empty($data['issuer']['identityProof'])) {
            return new VerificationResult('invalid_issuer', $data['issuer']['name'] ?? '');
        }

        $identityProofKey = $data['issuer']['identityProof']['key'] ?? '';
        $identityProofLocation = $data['issuer']['identityProof']['location'] ?? '';

        // Check DNS TXT record
        $dnsRecord = Http::get("https://dns.google/resolve?name={$identityProofLocation}&type=TXT");
        $txtRecords = $dnsRecord->json()['Answer'] ?? [];

        $found = false;
        foreach ($txtRecords as $record) {
            if (strpos($record['data'], $identityProofKey) !== false) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            Verification::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'file_type' => $fileType,
                'verification_result' => 'invalid_issuer',
                'timestamp' => now(),
            ]);
            return new VerificationResult('invalid_issuer', $data['issuer']['name'] ?? '');
        }

        // Condition 3: Validate signature.targetHash
        $computedHash = $this->computeTargetHash($data);

        if ($computedHash === $signature['targetHash']) {
            Verification::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'file_type' => $fileType,
                'verification_result' => 'verified',
                'timestamp' => now(),
            ]);
            return new VerificationResult('verified', $data['issuer']['name'] ?? '');
        }

        Verification::create([
            'user_id' => $request->user() ? $request->user()->id : null,
            'file_type' => $fileType,
            'verification_result' => 'invalid_signature',
            'timestamp' => now(),
        ]);
        return new VerificationResult('invalid_signature', $data['issuer']['name'] ?? '');
    }

    private function computeTargetHash(array $data): string
    {
        $propertyHashes = [];

        // Traverse and compute individual property hashes
        $this->traverseAndHash($data, '', $propertyHashes);

        // Sort the hashes alphabetically
        sort($propertyHashes);

        // Combine the sorted hashes into a JSON-encoded string
        $combinedHashes = json_encode($propertyHashes);

        // Test Hash results
        // dd($propertyHashes, $combinedHashes, hash('sha256', $combinedHashes));

        // Compute the final hash of the combined hashes
        return hash('sha256', $combinedHashes);
    }

    private function traverseAndHash(array $data, string $path, array &$hashes): void
    {
        foreach ($data as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : $key;

            if (is_array($value)) {
                $this->traverseAndHash($value, $currentPath, $hashes);
            } else {
                $hashes[] = hash('sha256', json_encode([$currentPath => $value]));
            }
        }
    }
}
