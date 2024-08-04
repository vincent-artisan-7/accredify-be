import React from 'react';

interface VerificationResultProps {
    status: 'verified' | 'invalid_signature' | 'invalid_recipient' | 'invalid_issuer';
    issuerName: string;
}

const VerificationResult: React.FC<VerificationResultProps> = ({ status, issuerName }) => {
    let message: string;

    switch (status) {
        case 'verified':
            message = `Verification successful for issuer: ${issuerName}`;
            break;
        case 'invalid_signature':
            message = `Invalid signature for issuer: ${issuerName}`;
            break;
        case 'invalid_recipient':
            message = `Invalid recipient details for issuer: ${issuerName}`;
            break;
        case 'invalid_issuer':
            message = `Invalid issuer details: ${issuerName}`;
            break;
        default:
            message = 'Unknown verification status';
    }

    return (
        <div className="p-4 mb-4 text-sm rounded-lg"
            style={{ backgroundColor: status === 'verified' ? '#d4edda' : '#f8d7da' }}>
            <p className="font-medium">{message}</p>
        </div>
    );
};

export default VerificationResult;
