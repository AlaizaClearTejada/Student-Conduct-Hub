<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request)
    {
        $prefs = $request->user()->notificationPreference ?? new NotificationPreference([
            'sms_enabled' => true,
            'email_enabled' => true,
            'in_app_enabled' => true,
            'max_sms_per_day' => 5,
        ]);

        return response()->json([
            'sms_enabled' => $prefs->sms_enabled,
            'email_enabled' => $prefs->email_enabled,
            'in_app_enabled' => $prefs->in_app_enabled,
            'max_sms_per_day' => $prefs->max_sms_per_day,
            'quiet_hours' => [
                'start' => $prefs->quiet_hours_start,
                'end' => $prefs->quiet_hours_end,
            ],
            'opted_out_events' => $prefs->opted_out_events ?? [],
            'phone_number' => $prefs->phone_number,
            'verified_phone' => $prefs->verified_phone,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'sms_enabled' => 'boolean',
            'email_enabled' => 'boolean',
            'max_sms_per_day' => 'integer|min:1',
            'quiet_hours' => 'array',
            'quiet_hours.start' => 'date_format:H:i',
            'quiet_hours.end' => 'date_format:H:i',
        ]);

        $prefs = $request->user()->notificationPreference()->firstOrCreate([]);

        $updateData = [];
        if (isset($validated['sms_enabled'])) {
            $updateData['sms_enabled'] = $validated['sms_enabled'];
        }
        if (isset($validated['email_enabled'])) {
            $updateData['email_enabled'] = $validated['email_enabled'];
        }
        if (isset($validated['max_sms_per_day'])) {
            $updateData['max_sms_per_day'] = $validated['max_sms_per_day'];
        }

        if (isset($validated['quiet_hours'])) {
            $updateData['quiet_hours_start'] = $validated['quiet_hours']['start'] ?? null;
            $updateData['quiet_hours_end'] = $validated['quiet_hours']['end'] ?? null;
        }

        $prefs->update($updateData);

        return response()->json(['success' => true]);
    }
}
