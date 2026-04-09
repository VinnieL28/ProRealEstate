<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessInboundResponseJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundResponseController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'lead_type' => 'required|string',
            'lead_id' => 'required|integer',
            'message' => 'required|string',
            'channel' => 'nullable|string',
        ]);

        ProcessInboundResponseJob::dispatch(
            $data['lead_type'],
            (int) $data['lead_id'],
            $data['message'],
            $data['channel'] ?? 'manual'
        );

        return response()->json(['status' => 'queued']);
    }
}
