<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource;
use Dashed\DashedCore\Filament\Concerns\HasCreatableCMSActions;

class CreateVacancy extends CreateRecord
{
    use HasCreatableCMSActions;

    protected static string $resource = VacancyResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
