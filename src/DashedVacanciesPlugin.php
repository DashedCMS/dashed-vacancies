<?php

namespace Dashed\DashedVacancies;

use Filament\Panel;
use Filament\Contracts\Plugin;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource;
use Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource;
use Dashed\DashedVacancies\Filament\Pages\Settings\VacanciesSettingsPage;

class DashedVacanciesPlugin implements Plugin
{
    public function getId(): string
    {
        return 'dashed-vacancies';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                VacancyResource::class,
                VacancyCategoryResource::class,
            ])
            ->pages([
                VacanciesSettingsPage::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
    }
}
