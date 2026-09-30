<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ChatBrainService
{
    private array $classes = [];

    private array $classDocCounts = [];

    private array $classWordCounts = [];

    private array $wordCountsByClass = [];

    private array $vocabulary = [];

    private int $totalDocs = 0;

    private string $modelPath = 'jam_brain.json';

    /**
     * Train the Naive Bayes model with samples and their corresponding labels (classes).
     *
     * @param  array<string>  $samples  Array of text samples (e.g., ["what is the penalty for cheating", ...])
     * @param  array<string>  $labels  Array of labels matching samples (e.g., ["AV-01", ...])
     */
    public function train(array $samples, array $labels): void
    {
        if (count($samples) !== count($labels)) {
            throw new \InvalidArgumentException('Samples and labels must have the same number of elements.');
        }

        $this->reset();

        foreach ($samples as $index => $text) {
            $class = $labels[$index];
            $words = $this->tokenize($text);

            if (! in_array($class, $this->classes)) {
                $this->classes[] = $class;
                $this->classDocCounts[$class] = 0;
                $this->classWordCounts[$class] = 0;
                $this->wordCountsByClass[$class] = [];
            }

            $this->classDocCounts[$class]++;
            $this->totalDocs++;

            foreach ($words as $word) {
                if (! isset($this->vocabulary[$word])) {
                    $this->vocabulary[$word] = true;
                }

                if (! isset($this->wordCountsByClass[$class][$word])) {
                    $this->wordCountsByClass[$class][$word] = 0;
                }

                $this->wordCountsByClass[$class][$word]++;
                $this->classWordCounts[$class]++;
            }
        }
    }

    /**
     * Predict the most likely class for the given text.
     *
     * @return string|null The predicted class label, or null if uncertain/empty.
     */
    public function predict(string $text): ?string
    {
        if (empty($this->classes) || $this->totalDocs === 0) {
            return null;
        }

        $words = $this->tokenize($text);
        if (empty($words)) {
            return null;
        }

        $validWords = [];
        foreach ($words as $word) {
            if (isset($this->vocabulary[$word])) {
                $validWords[] = $word;
            } else {
                // Fuzzy matching for out-of-vocabulary words (typos)
                $closestWord = null;
                $shortestDistance = -1;
                foreach ($this->vocabulary as $vWord => $true) {
                    // Only match reasonably close words
                    if (abs(strlen($word) - strlen($vWord)) > 2) {
                        continue;
                    }

                    $dist = levenshtein($word, $vWord);
                    if ($dist <= 2) {
                        if ($shortestDistance === -1 || $dist < $shortestDistance) {
                            $shortestDistance = $dist;
                            $closestWord = $vWord;
                        }
                    }
                }
                if ($closestWord !== null) {
                    $validWords[] = $closestWord;
                }
            }
        }

        if (empty($validWords)) {
            return null;
        }

        $vocabSize = count($this->vocabulary);
        $bestClass = null;
        $maxScore = -INF;

        // Calculate scores using log probabilities to avoid underflow
        foreach ($this->classes as $class) {
            // P(Class)
            $classProb = log($this->classDocCounts[$class] / $this->totalDocs);
            $score = $classProb;

            foreach ($validWords as $word) {
                // P(Word | Class) with Laplace (add-1) smoothing
                $wordCountInClass = $this->wordCountsByClass[$class][$word] ?? 0;
                $totalWordsInClass = $this->classWordCounts[$class];

                $wordProb = log(($wordCountInClass + 1) / ($totalWordsInClass + $vocabSize));
                $score += $wordProb;
            }

            if ($score > $maxScore) {
                $maxScore = $score;
                $bestClass = $class;
            }
        }

        return $bestClass;
    }

    /**
     * Save the trained model to the local disk.
     */
    public function saveModel(): void
    {
        $data = [
            'classes' => $this->classes,
            'classDocCounts' => $this->classDocCounts,
            'classWordCounts' => $this->classWordCounts,
            'wordCountsByClass' => $this->wordCountsByClass,
            'vocabulary' => $this->vocabulary,
            'totalDocs' => $this->totalDocs,
        ];

        Storage::disk('local')->put($this->modelPath, json_encode($data));
    }

    /**
     * Load the trained model from the local disk.
     *
     * @return bool True if loaded successfully, false otherwise.
     */
    public function loadModel(): bool
    {
        if (! Storage::disk('local')->exists($this->modelPath)) {
            return false;
        }

        $json = Storage::disk('local')->get($this->modelPath);
        $data = json_decode($json, true);

        if (! $data) {
            return false;
        }

        $this->classes = $data['classes'] ?? [];
        $this->classDocCounts = $data['classDocCounts'] ?? [];
        $this->classWordCounts = $data['classWordCounts'] ?? [];
        $this->wordCountsByClass = $data['wordCountsByClass'] ?? [];
        $this->vocabulary = $data['vocabulary'] ?? [];
        $this->totalDocs = $data['totalDocs'] ?? 0;

        return true;
    }

    private function reset(): void
    {
        $this->classes = [];
        $this->classDocCounts = [];
        $this->classWordCounts = [];
        $this->wordCountsByClass = [];
        $this->vocabulary = [];
        $this->totalDocs = 0;
    }

    /**
     * Tokenize text into an array of lowercase words.
     */
    private function tokenize(string $text): array
    {
        $text = strtolower($text);
        // Remove punctuation and special characters, keep letters and numbers
        $text = preg_replace('/[^a-z0-9\s]/', ' ', $text);

        $words = preg_split('/\s+/', trim($text));

        // Filter out short words and common stop words to improve accuracy
        $stopWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'to', 'in', 'of', 'it', 'for', 'with', 'as', 'by', 'that', 'this', 'are', 'was', 'were'];

        return array_values(array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 2 && ! in_array($word, $stopWords);
        }));
    }
}
