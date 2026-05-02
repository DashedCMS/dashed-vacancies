<div class="rounded-2xl border border-gray-200 p-6 space-y-4 bg-white">
    <h2 class="text-lg font-bold">{{ Translation::get('vacancy-summary', 'vacancies', 'Samenvatting') }}</h2>
    <dl class="space-y-3 text-sm">
        @if($vacancy->employment_types_label)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-employment', 'vacancies', 'Dienstverband') }}</dt>
                <dd>{{ $vacancy->employment_types_label }}</dd>
            </div>
        @endif
        @if($vacancy->job_location_type_label)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-location-type', 'vacancies', 'Werkvorm') }}</dt>
                <dd>{{ $vacancy->job_location_type_label }}</dd>
            </div>
        @endif
        @if($vacancy->city)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-city', 'vacancies', 'Locatie') }}</dt>
                <dd>{{ $vacancy->city }}{{ $vacancy->country ? ', ' . $vacancy->country : '' }}</dd>
            </div>
        @endif
        @if($vacancy->salary_display)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-salary', 'vacancies', 'Salaris') }}</dt>
                <dd>{{ $vacancy->salary_display }}</dd>
            </div>
        @endif
        @if($vacancy->work_hours_min || $vacancy->work_hours_max)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-hours', 'vacancies', 'Uren') }}</dt>
                <dd>
                    @if($vacancy->work_hours_min && $vacancy->work_hours_max && $vacancy->work_hours_min != $vacancy->work_hours_max)
                        {{ $vacancy->work_hours_min }} - {{ $vacancy->work_hours_max }} {{ Translation::get('vacancy-hours-per-week', 'vacancies', 'uur per week') }}
                    @else
                        {{ $vacancy->work_hours_max ?: $vacancy->work_hours_min }} {{ Translation::get('vacancy-hours-per-week', 'vacancies', 'uur per week') }}
                    @endif
                </dd>
            </div>
        @endif
        @if($vacancy->experience_level)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-experience-level', 'vacancies', 'Niveau') }}</dt>
                <dd>{{ ucfirst($vacancy->experience_level) }}</dd>
            </div>
        @endif
        @if($vacancy->application_deadline)
            <div>
                <dt class="font-semibold">{{ Translation::get('vacancy-deadline', 'vacancies', 'Sluitingsdatum') }}</dt>
                <dd>{{ $vacancy->application_deadline->format('d-m-Y') }}</dd>
            </div>
        @endif
    </dl>
</div>
