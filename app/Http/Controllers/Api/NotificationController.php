<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = Notification::where('recipient_id', $request->user()->id);

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('limit', 20));

        return response()->json([
            'data' => $notifications->items(),
            'pagination' => [
                'total' => $notifications->total(),
                'per_page' => $notifications->perPage(),
                'current_page' => $notifications->currentPage(),
            ],
        ]);
    }

    public function test(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'template_key' => 'required|string',
            'channels' => 'required|array',
        ]);

        // Simulating the test dispatch (would normally use a dedicated Service method or Event)
        // Here we just return a stub structure as per API spec
        return response()->json([
            'success' => true,
            'notification_id' => rand(100, 999),
            'channels' => [
                'sms' => [
                    'status' => 'queued',
                    'timestamp' => now()->toIso8601String(),
                ],
                'email' => [
                    'status' => 'queued',
                    'timestamp' => now()->toIso8601String(),
                ],
            ],
        ]);
    }
}
