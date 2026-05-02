<article class="relative isolate flex flex-col justify-end overflow-hidden rounded-2xl bg-gray-900 px-8 pb-8 pt-72 transform hover:scale-105 transition-all ease-in-out duration-300">
    @if($vacancy->image)
        <x-dashed-files::image
            class="absolute inset-0 -z-10 h-full w-full object-cover"
            :mediaId="$vacancy->image"
            :manipulations="['widen' => 800]"
        />
    @endif
    <div class="absolute inset-0 -z-10 bg-linear-to-tr from-primary-500 via-primary-500/60"></div>
    <div class="absolute inset-0 -z-10 rounded-2xl ring-1 ring-inset ring-black/10"></div>

    @if($vacancy->category)
        <div class="bg-primary-800 px-4 py-1 rounded-full text-xs font-semibold leading-5 text-white absolute top-8 left-8">
            {{ $vacancy->category->name }}
        </div>
    @endif

    <div class="flex flex-wrap gap-2 text-xs text-white/90">
        @if($vacancy->city)
            <span>{{ $vacancy->city }}</span>
        @endif
        @if($vacancy->employment_types_label)
            <span>·</span>
            <span>{{ $vacancy->employment_types_label }}</span>
        @endif
    </div>

    @if($vacancy->excerpt)
        <p class="mt-2 text-sm leading-6 text-white/90 line-clamp-2">{{ $vacancy->excerpt }}</p>
    @endif

    <h3 class="mt-3 text-lg font-semibold leading-6 text-white">
        <a href="{{ $vacancy->getUrl() }}">
            <span class="absolute inset-0"></span>
            {{ $vacancy->name }}
        </a>
    </h3>
</article>
