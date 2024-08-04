<?php

namespace App\DTO;

class VerificationResult
{
    private string $result;
    private string $issuer;

    public function __construct(string $result, string $issuer)
    {
        $this->result = $result;
        $this->issuer = $issuer;
    }

    public function toArray(): array
    {
        return [
            'data' => [
                'issuer' => $this->issuer,
                'result' => $this->result,
            ],
        ];
    }
}