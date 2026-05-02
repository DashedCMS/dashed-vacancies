@php
    $vacancyId = $data['vacancy_id'] ?? null;
    $vacancy = $vacancyId ? \Dashed\DashedVacancies\Models\Vacancy::find($vacancyId) : null;
@endphp

@if($vacancy)
    <div class="@if($data['top_margin'] ?? true) pt-16 sm:pt-24 @endif @if($data['bottom_margin'] ?? true) pb-16 sm:pb-24 @endif">
        <x-container :show="$data['in_container'] ?? true">
            <article class="grid lg:grid-cols-2 gap-8 items-center">
                @if($vacancy->image)
                    <x-dashed-files::image
                        class="rounded-2xl"
                        :mediaId="$vacancy->image"
                        :alt="$vacancy->name"
                        :manipulations="['widen' => 800]"
                    />
                @endif
                <div>
                    @if($vacancy->category)
                        <p class="text-sm font-semibold text-primary">{{ $vacancy->category->name }}</p>
                    @endif
                    <h2 class="mt-2 text-3xl md:text-4xl font-bold tracking-tight">{{ $vacancy->name }}</h2>
                    @if($vacancy->excerpt)
                        <p class="mt-4 text-lg text-gray-700">{{ $vacancy->excerpt }}</p>
                    @endif
                    <div class="mt-6 flex flex-wrap gap-3 text-sm text-gray-600">
                        @if($vacancy->city)<span>📍 {{ $vacancy->city }}</span>@endif
                        @if($vacancy->employment_types_label)<span>· {{ $vacancy->employment_types_label }}</span>@endif
                    </div>
                    <a href="{{ $vacancy->getUrl() }}" class="button button--primary mt-8 inline-block">
                        {{ Translation::get('view-vacancy', 'vacancies', 'Bekijk vacature') }}
                    </a>
                </div>
            </article>
        </x-container>
    </div>
@endif
