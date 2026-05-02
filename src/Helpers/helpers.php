<?php

use Dashed\DashedVacancies\VacancyManager;

if (! function_exists('vacancies')) {
    function vacancies(): VacancyManager
    {
        return app(VacancyManager::class);
    }
}
