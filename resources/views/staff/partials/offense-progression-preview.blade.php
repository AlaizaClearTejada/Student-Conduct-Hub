<section class="rounded-xl border border-amber-200 bg-amber-50 p-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h4 class="font-bold text-amber-950">Prior Offense History</h4>
            <p class="mt-1 text-sm text-amber-900">
                Next level: <strong>{{ $offensePreview['next_level'] }}</strong>
            </p>
        </div>
        <div class="rounded-lg bg-white/80 px-3 py-2 text-sm">
            <span class="text-gray-600">Mapped sanction:</span>
            <strong class="text-gray-900">{{ $offensePreview['next_sanction'] ?: 'No tier configured' }}</strong>
        </div>
    </div>

    @if($offensePreview['records'])
        <ul class="mt-4 divide-y divide-amber-200">
            @foreach($offensePreview['records'] as $record)
                <li class="flex flex-col gap-1 py-3 text-sm sm:flex-row sm:items-center sm:justify-between">
                    <span class="font-medium text-gray-900">
                        {{ $record['offense_level'] }} · {{ $record['incident_date'] }} · {{ $record['case_number'] }}
                    </span>
                    <span class="text-gray-700">
                        {{ $record['status'] }} · {{ $record['sanction'] ?: 'Sanction not assigned' }}
                    </span>
                </li>
            @endforeach
        </ul>
    @else
        <p class="mt-3 text-sm text-amber-900">No previous accepted cases for this offense rule.</p>
    @endif
</section>
