<?php

namespace App\Console\Commands;

use App\Models\OffenseRule;
use App\Services\ChatBrainService;
use Illuminate\Console\Command;

class TrainJamBrainCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jam:train';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Train the custom ChatBrain model using offense rules data';

    /**
     * Execute the console command.
     */
    public function handle(ChatBrainService $brain)
    {
        $this->info('Fetching offense rules for training...');

        $offenses = OffenseRule::query()->where('is_active', true)->get();

        if ($offenses->isEmpty()) {
            $this->error('No active offense rules found. Cannot train brain.');

            return;
        }

        $samples = [];
        $labels = [];

        foreach ($offenses as $offense) {
            $code = $offense->code;

            // Basic samples from the rule's title and description
            $samples[] = $offense->title;
            $labels[] = $code;

            if (! empty($offense->description)) {
                $samples[] = $offense->description;
                $labels[] = $code;
            }

            // Add some conversational variations based on title keywords
            $samples[] = 'What is the penalty for '.strtolower($offense->title).'?';
            $labels[] = $code;

            $samples[] = 'I got caught '.strtolower($offense->title).'. What will happen?';
            $labels[] = $code;

            $samples[] = 'Is '.strtolower($offense->title).' a major offense?';
            $labels[] = $code;
        }

        // Add some general conversational intents (like greetings) so the bot doesn't crash on "hello"
        $generalIntents = [
            'greeting' => ['hello', 'hi', 'hey there', 'good morning', 'good afternoon', 'help me'],
        ];

        foreach ($generalIntents as $intent => $phrases) {
            foreach ($phrases as $phrase) {
                $samples[] = $phrase;
                $labels[] = $intent;
            }
        }

        $this->info(sprintf('Training ChatBrain with %d samples...', count($samples)));

        $brain->train($samples, $labels);
        $brain->saveModel();

        $this->info('ChatBrain trained and saved successfully!');
    }
}
