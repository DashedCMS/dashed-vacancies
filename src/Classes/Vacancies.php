<?php

namespace Dashed\DashedVacancies\Classes;

use Dashed\DashedVacancies\Models\Vacancy;

class Vacancies
{
    public static function get(int $limit = 4, string $orderBy = 'created_at', string $order = 'DESC', int $exceptId = 0, array $categoryIds = [])
    {
        $query = Vacancy::query()
            ->where('id', '!=', $exceptId)
            ->thisSite()
            ->publicShowable();

        if ($categoryIds) {
            $query->whereIn('category_id', $categoryIds);
        }

        return $query->limit($limit)->orderBy($orderBy, $order)->get();
    }

    public static function getAll(int $pagination = 12, string $orderBy = 'created_at', string $order = 'DESC')
    {
        return Vacancy::thisSite()
            ->publicShowable()
            ->orderBy($orderBy, $order)
            ->paginate($pagination)
            ->withQueryString();
    }

    public static function getOverviewUrl(): ?string
    {
        $page = Vacancy::getOverviewPage();

        return $page ? $page->getUrl() : '#';
    }
}
