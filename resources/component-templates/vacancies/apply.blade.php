<div id="apply" class="rounded-2xl bg-gray-50 p-8">
    <h2 class="text-2xl font-bold mb-6">{{ Translation::get('vacancy-apply-title', 'vacancies', 'Solliciteer direct') }}</h2>

    @if($vacancy->direct_apply && $vacancy->form)
        <livewire:dashed-forms.form :form="$vacancy->form" />
    @elseif($vacancy->application_url)
        <a href="{{ $vacancy->application_url }}" class="button button--primary" target="_blank" rel="noopener">
            {{ Translation::get('vacancy-apply-cta', 'vacancies', 'Solliciteer nu') }}
        </a>
    @elseif($vacancy->application_email)
        <a href="mailto:{{ $vacancy->application_email }}?subject={{ urlencode('Sollicitatie ' . $vacancy->name) }}" class="button button--primary">
            {{ Translation::get('vacancy-apply-mail', 'vacancies', 'Mail je sollicitatie') }}
        </a>
    @else
        <p class="text-gray-600">{{ Translation::get('vacancy-apply-empty', 'vacancies', 'Neem contact met ons op om te solliciteren.') }}</p>
    @endif
</div>
