<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;

class EditVacancyCategory extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = VacancyCategoryResource::class;

    protected function getActions(): array
    {
        return self::CMSActions();
    }
}
