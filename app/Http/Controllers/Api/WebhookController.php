<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationChannelLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function smsDeliveryStatus(Request $request)
    {
        // verify signature would go here

        $log = NotificationChannelLog::where('external_id', $request->input('message_id'))->first();
        if ($log) {
            $log->update([
                'status' => $request->input('status') === 'delivered' ? 'delivered' : 'failed',
                'delivered_at' => $request->input('delivered_at'),
                'cost' => $request->input('cost'),
            ]);
        }

        return response()->json(['received' => true]);
    }

    public function emailEvents(Request $request)
    {
        // verify signature would go here

        $eventType = $request->input('eventType');
        if ($eventType === 'Bounce') {
            Log::info('Email bounce webhook received');
        }

        return response()->json(['received' => true]);
    }
}
