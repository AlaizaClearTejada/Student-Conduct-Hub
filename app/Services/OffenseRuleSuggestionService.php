<?php

namespace App\Services;

use App\Models\OffenseRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class OffenseRuleSuggestionService
{
    /**
     * @return Collection<int, OffenseRule>
     */
    public function suggest(string $search, bool $majorOnly = false): Collection
    {
        $search = mb_strtolower(trim($search));
        $terms = collect(preg_split('/[^\pL\pN-]+/u', $search) ?: [])
            ->filter(fn (string $term): bool => mb_strlen($term) >= 2)
            ->unique()
            ->take(12)
            ->values();

        if ($terms->isEmpty()) {
            return new Collection;
        }

        $query = OffenseRule::query()->active();

        if ($majorOnly) {
            $query->requiringTribunalReview();
        }

        $matches = $query
            ->where(function (Builder $query) use ($terms): void {
                foreach ($terms as $term) {
                    $pattern = '%'.$term.'%';
                    $query->orWhereRaw('LOWER(code) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(title) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(category) LIKE ?', [$pattern])
                        ->orWhereRaw('LOWER(description) LIKE ?', [$pattern]);
                }
            })
            ->limit(50)
            ->get();

        return $matches
            ->sortByDesc(fn (OffenseRule $offense): int => $this->relevance($offense, $search, $terms))
            ->take(8)
            ->values();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, string>  $terms
     */
    private function relevance(OffenseRule $offense, string $search, \Illuminate\Support\Collection $terms): int
    {
        $title = mb_strtolower($offense->title);
        $category = mb_strtolower($offense->category);
        $code = mb_strtolower($offense->code);
        $description = mb_strtolower($offense->description ?? '');
        $score = str_contains($title, $search) ? 20 : 0;

        foreach ($terms as $term) {
            $score += (str_contains($title, $term) ? 8 : 0)
                + (str_contains($category, $term) ? 6 : 0)
                + (str_contains($code, $term) ? 10 : 0)
                + (str_contains($description, $term) ? 1 : 0);
        }

        return $score;
    }
}
