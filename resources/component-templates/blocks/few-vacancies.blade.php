<div class="@if($data['top_margin'] ?? true) pt-16 sm:pt-24 @endif @if($data['bottom_margin'] ?? true) pb-16 sm:pb-24 @endif">
    <x-container :show="$data['in_container'] ?? true">
        @if(($data['title'] ?? false) || ($data['subtitle'] ?? false))
            <header class="flex flex-wrap gap-4 items-center justify-between">
                <div>
                    @if($data['title'] ?? false)
                        <h2 class="shrink-0 tracking-tight text-4xl text-balance font-brand">
                            {{ $data['title'] }}
                        </h2>
                    @endif
                    @if($data['subtitle'] ?? false)
                        <p class="mt-2 text-lg leading-8 text-black">{{ $data['subtitle'] }}</p>
                    @endif
                </div>
                <div>
                    <a class="button button--primary" href="{{ \Dashed\DashedVacancies\Models\Vacancy::getOverviewPage()?->url ?? '#' }}">
                        {{ Translation::get('view-all-vacancies', 'vacancies', 'Bekijk alle vacatures') }}
                    </a>
                </div>
            </header>
        @endif
        <div class="mx-auto grid max-w-2xl auto-rows-fr grid-cols-1 gap-8 mt-12 lg:mx-0 lg:max-w-none lg:grid-cols-3">
            @php($amount = (int) ($data['amount'] ?? 3))
            @foreach(\Dashed\DashedVacancies\Classes\Vacancies::get($amount, 'created_at', 'DESC') as $vacancy)
                <x-vacancy :vacancy="$vacancy" />
            @endforeach
        </div>
    </x-container>
</div>
