<?php

namespace Dashed\DashedVacancies\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class VacancyPolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'Vacancy';
    }
}
