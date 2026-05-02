<x-master>
    <section class="relative py-16">
        <x-container>
            <header class="flex flex-col items-center text-center">
                <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight">{{ $category->name }}</h1>
            </header>

            <div class="mt-12">
                <livewire:vacancies.show-vacancies :category="$category->id" />
            </div>
        </x-container>
    </section>
</x-master>
