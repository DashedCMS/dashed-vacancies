<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;

class CreateVacancyCategory extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = VacancyCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
