<?php

namespace App\Http\Controllers;

use App\Models\OffenseRule;
use App\Services\ChatBrainService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StudentChatController extends Controller
{
    public function chat(Request $request, ChatBrainService $brain): JsonResponse
    {
        $validated = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:40'],
            'messages.*.role' => ['required', 'string', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
            'lang' => ['required', 'string', 'in:en,fil'],
            'studentName' => ['required', 'string', 'max:100'],
        ]);

        if (! $brain->loadModel()) {
            return response()->json([
                'error' => 'The AI assistant brain is not trained yet. Please run php artisan jam:train to train it.',
            ], 503);
        }

        // Get the last user message
        $messages = collect($validated['messages']);
        $lastMessage = $messages->where('role', 'user')->last();
        $text = $lastMessage['content'] ?? '';

        $isFil = $validated['lang'] === 'fil';
        $studentName = trim(preg_replace('/[\r\n\t\x00-\x1F\x7F]/', ' ', $validated['studentName']));

        $prediction = $brain->predict($text);

        if (! $prediction) {
            $reply = $isFil
                ? 'Paumanhin, hindi ko masyadong naintindihan iyon. Maaari mo bang ipaliwanag nang kaunti pa ang tungkol sa paglabag na tinatanong mo?'
                : "I'm sorry, I didn't quite catch that. Could you provide a bit more detail about the offense you're asking about?";

            return response()->json(['reply' => $reply]);
        }

        if ($prediction === 'greeting') {
            $reply = $isFil
                ? "Kamusta {$studentName}! Ako si JAM, ang opisyal na CSU Student Conduct Policy Assistant. Paano kita matutulungan ngayon tungkol sa mga patakaran ng campus?"
                : "Hello {$studentName}! I am JAM, the official CSU Student Conduct Policy Assistant. How can I help you today regarding campus policies?";

            return response()->json(['reply' => $reply]);
        }

        // It predicted an offense code. Fetch the offense.
        $offense = OffenseRule::where('code', $prediction)->first();

        if (! $offense) {
            $reply = $isFil
                ? 'Mukhang nagtatanong ka tungkol sa isang patakaran, pero hindi ko makita ang eksaktong detalye. Makipag-ugnayan sa OSA para sa karagdagang tulong.'
                : "It seems you are asking about a policy, but I can't find the exact details. Please contact the OSA for further assistance.";

            return response()->json(['reply' => $reply]);
        }

        // Format the response based on the offense
        $reply = $this->formatOffenseResponse($offense, $isFil);

        return response()->json(['reply' => $reply]);
    }

    private function formatOffenseResponse(OffenseRule $offense, bool $isFil): string
    {
        $tribunal = $offense->requires_tribunal
            ? ($isFil ? "\n\n⚠️ **[NANGANGAILANGAN NG TRIBUNA]** Ang paglabag na ito ay seryoso at nangangailangan ng pormal na pagdinig ng Student Disciplinary Tribunal." : "\n\n⚠️ **[REQUIRES TRIBUNAL]** This offense is serious enough to require a formal Student Disciplinary Tribunal hearing.")
            : '';

        if ($isFil) {
            return <<<REPLY
Batay sa iyong mensahe, mukhang tinutukoy mo ang paglabag na **{$offense->code}**: {$offense->title}.

Narito ang mga detalye mula sa Student Conduct Manual:
- **Kategorya:** {$offense->category}
- **Kalubhaan:** {$offense->severity_level} (Gravity: {$offense->gravity})
- **Paglalarawan:** {$offense->description}

**Mga Nakatakdang Parusa:**
- 1st Offense: {$offense->first_offense_sanction}
- 2nd Offense: {$offense->second_offense_sanction}
- 3rd Offense: {$offense->third_offense_sanction}
{$tribunal}

*Para sa pormal o partikular sa kaso na gabay, makipag-ugnayan sa OSA sa osa@csu.edu.ph. Mayroon kang karapatang sa maayos na proseso ng batas.*
REPLY;
        }

        return <<<REPLY
Based on your message, it sounds like you are asking about **{$offense->code}**: {$offense->title}.

Here are the details from the Student Conduct Manual:
- **Category:** {$offense->category}
- **Severity:** {$offense->severity_level} (Gravity: {$offense->gravity})
- **Description:** {$offense->description}

**Applicable Sanctions:**
- 1st Offense: {$offense->first_offense_sanction}
- 2nd Offense: {$offense->second_offense_sanction}
- 3rd Offense: {$offense->third_offense_sanction}
{$tribunal}

*For formal or case-specific guidance, please contact the OSA at osa@csu.edu.ph. You have the right to proper due process.*
REPLY;
    }
}
