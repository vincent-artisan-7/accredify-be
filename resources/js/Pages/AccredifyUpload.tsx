import React, { ChangeEvent, FormEventHandler, useState } from 'react';
import { Head, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import VerificationResult from '@/Pages/VerificationResult';
import { PageProps } from '@/types';
import axios from 'axios';

// Get CSRF token from meta tag
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

// Configure Axios
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.withCredentials = true;

// Use the same type for both API response and component state
interface VerificationResultProps {
    result: 'verified' | 'invalid_signature' | 'invalid_recipient' | 'invalid_issuer';
    issuer: string;
}

const AccredifyUpload = () => {
    const { auth } = usePage<PageProps>().props;
    const [result, setResult] = useState<VerificationResultProps | null>(null);
    const [file, setFile] = useState<File | null>(null);
    const [uploadError, setUploadError] = useState<string | null>(null);

    const handleFileChange = (e: ChangeEvent<HTMLInputElement>) => {
        if (e.target.files) {
            setFile(e.target.files[0]);
        }
    };

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();

        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        try {
            const response = await axios.post('/api/verify-json', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            console.log('Response:', response.data);
            const responseData: VerificationResultProps = response.data.data;
            setResult({
                result: responseData.result,
                issuer: responseData.issuer,
            });
            setUploadError(null); // Clear previous errors
        } catch (error: any) {
            if (error.response && error.response.data && error.response.data.message) {
                setUploadError(error.response.data.message);
            } else {
                setUploadError('An unexpected error occurred.');
            }
        }
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Accredify Upload File</h2>}
        >
            <Head title="Upload File" />

            <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div className="p-6 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <InputLabel htmlFor="file" value="Upload JSON File" />
                                <TextInput
                                    id="file"
                                    type="file"
                                    name="file"
                                    className="mt-1 block w-full"
                                    onChange={handleFileChange}
                                />
                                {uploadError && (
                                    <InputError message={uploadError} className="mt-2" />
                                )}
                            </div>

                            <div className="flex items-center justify-end mt-4">
                                <PrimaryButton className="ms-4" disabled={!file}>
                                    Upload
                                </PrimaryButton>
                            </div>
                        </form>

                        {result && (
                            <div className="mt-4">
                                <VerificationResult
                                    status={result.result}
                                    issuerName={result.issuer}
                                />
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
};

export default AccredifyUpload;
