<?php

namespace Dashed\DashedVacancies\Policies;

use Dashed\DashedCore\Policies\BaseResourcePolicy;

class VacancyCategoryPolicy extends BaseResourcePolicy
{
    protected function resourceName(): string
    {
        return 'VacancyCategory';
    }
}
