<?php

namespace Dashed\DashedVacancies\Classes;

class VacancyForms
{
    public static function createApplicationForm(?string $vacancyName = null): mixed
    {
        if (! class_exists(\Dashed\DashedForms\Models\Form::class)) {
            return null;
        }

        $locale = app()->getLocale();
        $name = $vacancyName ? "Sollicitatie: {$vacancyName}" : 'Sollicitatie formulier';

        $form = \Dashed\DashedForms\Models\Form::create([
            'name' => $name,
        ]);

        $form->fields()->create([
            'name' => [$locale => 'Naam'],
            'type' => 'input',
            'input_type' => 'text',
            'required' => 1,
            'sort' => 1,
            'helper_text' => [],
        ]);

        $emailField = $form->fields()->create([
            'name' => [$locale => 'E-mailadres'],
            'type' => 'input',
            'input_type' => 'email',
            'required' => 1,
            'sort' => 2,
            'helper_text' => [],
        ]);

        $form->fields()->create([
            'name' => [$locale => 'Telefoonnummer'],
            'type' => 'input',
            'input_type' => 'text',
            'required' => 0,
            'sort' => 3,
            'helper_text' => [],
        ]);

        $form->fields()->create([
            'name' => [$locale => 'LinkedIn profiel'],
            'type' => 'input',
            'input_type' => 'text',
            'required' => 0,
            'sort' => 4,
            'helper_text' => [],
        ]);

        $form->fields()->create([
            'name' => [$locale => 'Motivatie'],
            'type' => 'textarea',
            'required' => 1,
            'sort' => 5,
            'placeholder' => [$locale => 'Vertel iets over jezelf en waarom je bij ons wilt komen werken'],
            'helper_text' => [],
        ]);

        $form->fields()->create([
            'name' => [$locale => 'CV'],
            'type' => 'file',
            'required' => 0,
            'sort' => 6,
            'helper_text' => [$locale => 'Upload je CV (PDF, DOC of DOCX)'],
        ]);

        $form->email_confirmation_form_field_id = $emailField->id;
        $form->save();

        return $form;
    }
}
