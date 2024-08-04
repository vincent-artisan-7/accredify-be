<?php
namespace App\Http\Controllers;

use App\DTO\VerificationResult;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class VerificationController extends Controller
{
    public function verifyJson(Request $request): JsonResponse
    {
        // $user = $request->user();
        // if (!$user) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // Validate the request
        $request->validate([
            'file' => 'required|file|mimes:json|max:2048',
        ]);

        // Store the file
        $file = $request->file('file');
        $content = file_get_contents($file->getPathname());
        $json = json_decode($content, true);

        if ($json === null) {
            $result = new VerificationResult('invalid_signature', '');
            return response()->json($result->toArray());
        }

        // Extract data from JSON
        $data = $json['data'] ?? [];
        $signature = $json['signature'] ?? [];

        // Condition 1: Validate data.recipient
        if (empty($data['recipient']['name']) || empty($data['recipient']['email'])) {
            $result = new VerificationResult('invalid_recipient', $data['issuer']['name'] ?? '');
            return response()->json($result->toArray());
        }

        // Condition 2: Validate data.issuer
        if (empty($data['issuer']['name']) || empty($data['issuer']['identityProof'])) {
            $result = new VerificationResult('invalid_issuer', $data['issuer']['name'] ?? '');
            return response()->json($result->toArray());
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
            $result = new VerificationResult('invalid_issuer', $data['issuer']['name'] ?? '');
            return response()->json($result->toArray());
        }

        // Condition 3: Validate signature.targetHash
        $computedHash = $this->computeTargetHash($data);

        if ($computedHash === $signature['targetHash']) {
            $result = new VerificationResult('verified', $data['issuer']['name'] ?? '');
            return response()->json($result->toArray());
        }

        $result = new VerificationResult('invalid_signature', $data['issuer']['name'] ?? '');
        return response()->json($result->toArray());
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
