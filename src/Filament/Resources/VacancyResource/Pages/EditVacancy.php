<?php

namespace Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Dashed\DashedVacancies\Classes\VacancyForms;
use Dashed\DashedCore\Filament\Concerns\HasEditableCMSActions;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource;

class EditVacancy extends EditRecord
{
    use HasEditableCMSActions;

    protected static string $resource = VacancyResource::class;

    protected function getActions(): array
    {
        return array_merge([
            Action::make('createApplicationForm')
                ->label(__('Sollicitatieformulier aanmaken'))
                ->icon('heroicon-o-document-plus')
                ->color('primary')
                ->button()
                ->requiresConfirmation()
                ->modalHeading(__('Sollicitatieformulier aanmaken'))
                ->modalDescription(__('Er wordt een standaard sollicitatieformulier aangemaakt (Naam, E-mail, Telefoonnummer, LinkedIn, Motivatie, CV) en automatisch aan deze vacature gekoppeld.'))
                ->modalSubmitActionLabel(__('Aanmaken en koppelen'))
                ->visible(fn () => class_exists(\Dashed\DashedForms\Models\Form::class) && empty($this->record->form_id))
                ->action(function () {
                    $form = VacancyForms::createApplicationForm($this->record->name);

                    if (! $form) {
                        Notification::make()
                            ->title(__('Aanmaken mislukt'))
                            ->body(__('Er ging iets mis bij het aanmaken van het sollicitatieformulier.'))
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->record->form_id = $form->id;
                    if (! $this->record->direct_apply) {
                        $this->record->direct_apply = true;
                    }
                    $this->record->save();
                    $this->record->refresh();

                    $this->fillForm();

                    Notification::make()
                        ->title(__('Sollicitatieformulier aangemaakt'))
                        ->body(__('Het formulier ":naam" is gekoppeld aan deze vacature.', ['naam' => $form->name]))
                        ->success()
                        ->send();
                }),
        ], self::CMSActions());
    }
}
