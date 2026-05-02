<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use LaraZeus\SpatieTranslatable\Actions\LocaleSwitcher;
use Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource;
use LaraZeus\SpatieTranslatable\Resources\Pages\ListRecords\Concerns\Translatable;

class ListVacancyCategories extends ListRecords
{
    use Translatable;

    protected static string $resource = VacancyCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            LocaleSwitcher::make(),
            CreateAction::make(),
        ];
    }
}
