<?php

namespace App\Http\Controllers;

use App\Services\AiAlertService;
use Illuminate\Support\Facades\Auth;

class AIAlertController extends Controller
{
    public function run(AiAlertService $alerts)
    {
        $userId = 1; // 🔥 FORZADO PARA TEST

        $alerts->runForUser($userId);

        return response()->json([
            'status' => 'alerts_checked'
        ]);
    }
}
