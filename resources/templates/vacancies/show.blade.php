<x-master>
    <x-vacancies.schema :vacancy="$vacancy" />

    <article class="relative py-16">
        <x-container>
            <header class="flex flex-col items-center text-center">
                @if($vacancy->category)
                    <a href="{{ $vacancy->category->getUrl() }}" class="inline-block font-bold text-primary">
                        {{ $vacancy->category->name }}
                    </a>
                @endif

                <h1 class="mt-4 text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight max-w-[30ch]">
                    {{ $vacancy->name }}
                </h1>

                <div class="mt-6 flex flex-wrap justify-center gap-3 text-sm text-gray-700">
                    @if($vacancy->city)
                        <span class="inline-flex items-center gap-1">
                            <span class="font-semibold">📍</span> {{ $vacancy->city }}
                        </span>
                    @endif
                    @if($vacancy->employment_types_label)
                        <span>{{ $vacancy->employment_types_label }}</span>
                    @endif
                    @if($vacancy->job_location_type_label)
                        <span>{{ $vacancy->job_location_type_label }}</span>
                    @endif
                    @if($vacancy->salary_display)
                        <span>{{ $vacancy->salary_display }}</span>
                    @endif
                </div>

                @if($vacancy->image)
                    <x-dashed-files::image
                        class="mt-12 rounded"
                        config="dashed"
                        :mediaId="$vacancy->image"
                        :alt="$vacancy->name"
                        :manipulations="['widen' => 1000]"
                    />
                @endif
            </header>

            <div class="mt-12 grid gap-12 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-8">
                    @if($vacancy->description)
                        <section>
                            <div class="prose max-w-none">{!! nl2br(e($vacancy->description)) !!}</div>
                        </section>
                    @endif

                    @if($vacancy->responsibilities)
                        <section>
                            <h2 class="text-2xl font-bold mb-3">{{ Translation::get('vacancy-responsibilities', 'vacancies', 'Wat ga je doen?') }}</h2>
                            <div class="prose max-w-none">{!! nl2br(e($vacancy->responsibilities)) !!}</div>
                        </section>
                    @endif

                    @if($vacancy->requirements)
                        <section>
                            <h2 class="text-2xl font-bold mb-3">{{ Translation::get('vacancy-requirements', 'vacancies', 'Wat vragen wij?') }}</h2>
                            <div class="prose max-w-none">{!! nl2br(e($vacancy->requirements)) !!}</div>
                        </section>
                    @endif

                    @if($vacancy->benefits)
                        <section>
                            <h2 class="text-2xl font-bold mb-3">{{ Translation::get('vacancy-benefits', 'vacancies', 'Wat bieden wij?') }}</h2>
                            <div class="prose max-w-none">{!! nl2br(e($vacancy->benefits)) !!}</div>
                        </section>
                    @endif

                    <x-blocks :content="$vacancy->content"></x-blocks>
                </div>

                <aside class="space-y-6">
                    <x-vacancies.sidebar :vacancy="$vacancy" />
                </aside>
            </div>

            <div class="mt-16">
                <x-vacancies.apply :vacancy="$vacancy" />
            </div>
        </x-container>
    </article>

    <x-dashed-core::global-blocks name="vacancy-page"/>
</x-master>
