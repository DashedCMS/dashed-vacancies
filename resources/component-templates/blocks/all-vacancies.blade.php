@php($blockData = $data ?? [])
<div class="@if($blockData['top_margin'] ?? true) pt-16 sm:pt-24 @endif @if($blockData['bottom_margin'] ?? true) pb-16 sm:pb-24 @endif">
    <x-container :show="$blockData['in_container'] ?? true">
        @if($blockData['title'] ?? false)
            <header class="mb-12 text-center">
                <h2 class="tracking-tight text-4xl font-brand">{{ $blockData['title'] }}</h2>
                @if($blockData['subtitle'] ?? false)
                    <p class="mt-2 text-lg leading-8 text-black">{{ $blockData['subtitle'] }}</p>
                @endif
            </header>
        @endif
        <livewire:vacancies.show-vacancies />
    </x-container>
</div>
