<div>
    <x-container>
        <div class="mb-8 flex flex-wrap gap-3 items-center">
            <input type="search"
                   wire:model.live.debounce.400ms="search"
                   placeholder="{{ Translation::get('vacancy-search-placeholder', 'vacancies', 'Zoek een vacature') }}"
                   class="px-4 py-2 border rounded-md w-full md:w-64" />
            <select wire:model.live="employmentType" class="px-4 py-2 border rounded-md">
                <option value="">{{ Translation::get('vacancy-all-employment-types', 'vacancies', 'Alle dienstverbanden') }}</option>
                <option value="FULL_TIME">Fulltime</option>
                <option value="PART_TIME">Parttime</option>
                <option value="CONTRACTOR">Contractor</option>
                <option value="TEMPORARY">Tijdelijk</option>
                <option value="INTERN">Stage</option>
            </select>
            <select wire:model.live="jobLocationType" class="px-4 py-2 border rounded-md">
                <option value="">{{ Translation::get('vacancy-all-locations', 'vacancies', 'Alle werkvormen') }}</option>
                <option value="on-site">Op locatie</option>
                <option value="hybrid">Hybride</option>
                <option value="TELECOMMUTE">Op afstand</option>
            </select>
        </div>

        @if($vacancies->count())
            <div class="mx-auto grid max-w-2xl auto-rows-fr grid-cols-1 gap-8 lg:mx-0 lg:max-w-none lg:grid-cols-3">
                @foreach($vacancies as $vacancy)
                    <x-vacancy :vacancy="$vacancy"/>
                @endforeach
            </div>
            <div class="mt-8">
                {{ $vacancies->links('dashed.partials.pagination') }}
            </div>
        @else
            <p class="text-center py-12 text-gray-600">
                {{ Translation::get('vacancy-no-results', 'vacancies', 'Geen vacatures gevonden.') }}
            </p>
        @endif
    </x-container>
</div>
