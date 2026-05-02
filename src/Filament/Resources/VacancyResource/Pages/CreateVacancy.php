<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Dashed\DashedVacancies\Classes\VacancyForms;
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $autoCreate = (bool) ($data['auto_create_form'] ?? false);
        unset($data['auto_create_form']);

        if ($autoCreate && empty($data['form_id']) && class_exists(\Dashed\DashedForms\Models\Form::class)) {
            $form = VacancyForms::createApplicationForm($data['name'] ?? null);
            if ($form) {
                $data['form_id'] = $form->id;
            }
        }

        return $data;
    }
}
