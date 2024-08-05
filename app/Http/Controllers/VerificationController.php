<?php
namespace App\Http\Controllers;

use App\Services\JsonVerificationService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class VerificationController extends BaseController
{
    protected JsonVerificationService $jsonVerifyService;

    public function __construct()
    {
        // $this->middleware('auth');
        $this->jsonVerifyService = new JsonVerificationService();
    }

    public function verifyJson(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ]);
        }
        $verifyResult = $this->jsonVerifyService->verifyFile($request);

        return response()->json($verifyResult->toArray());
    }

}
