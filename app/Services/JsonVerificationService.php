<?php

namespace App\Services;

use App\DTO\VerificationResult;
use Illuminate\Support\Facades\Http;

class JsonVerificationService
{
    public function verifyJson(array $json): VerificationResult
    {
        // Validate recipient
        if (empty($json['data']['recipient']['name']) || empty($json['data']['recipient']['email'])) {
            return new VerificationResult('invalid_recipient', $json['data']['issuer']['name']);
        }

        // Validate issuer
        $issuer = $json['data']['issuer'];
        if (empty($issuer['name']) || empty($issuer['identityProof']['key']) || empty($issuer['identityProof']['location'])) {
            return new VerificationResult('invalid_issuer', $issuer['name']);
        }

        // Verify issuer's DNS record
        $dnsResponse = Http::get('https://dns.google/resolve', [
            'name' => $issuer['identityProof']['location'],
            'type' => 'TXT',
        ])->json();

        $key = $issuer['identityProof']['key'];
        $dnsValid = false;
        foreach ($dnsResponse['Answer'] as $record) {
            if (str_contains($record['data'], $key)) {
                $dnsValid = true;
                break;
            }
        }

        if (!$dnsValid) {
            return new VerificationResult('invalid_issuer', $issuer['name']);
        }

        // Validate signature
        $computedHash = $this->computeTargetHash($json['data']);
        if ($computedHash !== $json['signature']['targetHash']) {
            return new VerificationResult('invalid_signature', $issuer['name']);
        }

        return new VerificationResult('verified', $issuer['name']);
    }

    private function computeTargetHash(array $data): string
    {
        $flattened = $this->flatten($data);
        $hashes = array_map(fn($key, $value) => hash('sha256', json_encode([$key => $value])), array_keys($flattened), $flattened);
        sort($hashes);
        return hash('sha256', json_encode($hashes));
    }

    private function flatten(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $result += $this->flatten($value, $newKey);
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
}
