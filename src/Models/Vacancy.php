<?php

namespace Dashed\DashedVacancies\Models;

use Dashed\DashedPages\Models\Page;
use Illuminate\Support\Facades\App;
use Dashed\DashedCore\Classes\Sites;
use Illuminate\Support\Facades\View;
use Dashed\DashedCore\Classes\Locales;
use Illuminate\Database\Eloquent\Model;
use Dashed\DashedCore\Models\Customsetting;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;
use Dashed\LaravelLocalization\Facades\LaravelLocalization;

class Vacancy extends Model
{
    use HasCustomBlocks;
    use IsVisitable;
    use SoftDeletes;

    protected $table = 'dashed__vacancies';

    public $translatable = [
        'name',
        'slug',
        'excerpt',
        'content',
        'description',
        'responsibilities',
        'requirements',
        'benefits',
        'qualifications',
        'skills',
        'education_requirements',
        'experience_requirements',
        'industry',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'application_deadline' => 'datetime',
        'valid_through' => 'datetime',
        'site_ids' => 'array',
        'employment_types' => 'array',
        'content' => 'array',
        'public' => 'boolean',
        'direct_apply' => 'boolean',
        'salary_min' => 'decimal:2',
        'salary_max' => 'decimal:2',
    ];

    protected $appends = [
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(VacancyCategory::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(\Dashed\DashedForms\Models\Form::class, 'form_id');
    }

    public static function canHaveParent(): bool
    {
        return false;
    }

    public function getNextVacancy(bool $useFirstIfIsLast = true): ?Vacancy
    {
        $query = self::thisSite()->publicShowable();
        if ($this->category) {
            $query->where('category_id', $this->category_id);
        }

        $next = (clone $query)->where('id', '>', $this->id)->orderBy('id', 'ASC')->first();

        if (! $next && $useFirstIfIsLast) {
            $first = (clone $query)->orderBy('id', 'ASC')->first();
            if ($first && $first->id !== $this->id) {
                return $first;
            }
        }

        return $next;
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        if (empty($slug)) {
            return;
        }

        $slugComponents = explode('/', $slug);
        $lastSlugPart = $slugComponents[array_key_last($slugComponents)] ?? null;
        $secondLastSlugPart = $slugComponents[count($slugComponents) - 2] ?? null;

        $overviewPage = self::getOverviewPage();
        $vacancy = self::resolveVacancy($lastSlugPart);

        if (! $vacancy) {
            return;
        }

        if (! self::isValidSlugStructure($vacancy, $overviewPage, $slugComponents, $secondLastSlugPart)) {
            return;
        }

        if ($overviewPage) {
            $page = self::getPageIfExists($overviewPage, $slugComponents[0]);
            if (! $page) {
                return;
            }
        }

        return self::renderVacancyView($vacancy, $page ?? null);
    }

    private static function resolveVacancy($slug): ?Vacancy
    {
        return self::publicShowable()
            ->where('slug->' . App::getLocale(), $slug)
            ->first();
    }

    private static function isValidSlugStructure($vacancy, $overviewPage, $slugComponents, $secondLastSlugPart): bool
    {
        $useCategoryInUrl = Customsetting::get('vacancy_use_category_in_url', null, false);
        $expectedBaseCount = $overviewPage ? 2 : 1;

        return (! $useCategoryInUrl && count($slugComponents) === $expectedBaseCount)
            || (! $vacancy->category && count($slugComponents) === $expectedBaseCount)
            || ($useCategoryInUrl && $vacancy->category && $vacancy->category->slug === $secondLastSlugPart && count($slugComponents) > $expectedBaseCount);
    }

    private static function getPageIfExists($overviewPage, $firstSlugPart)
    {
        return Page::publicShowable()
            ->isNotHome()
            ->where('slug->' . App::getLocale(), $firstSlugPart)
            ->where('id', $overviewPage->id)
            ->first();
    }

    private static function renderVacancyView(Vacancy $vacancy, $page)
    {
        if (! View::exists(config('dashed-core.site_theme', 'dashed') . '.vacancies.show')) {
            return 'pageNotFound';
        }

        self::setSeoMetadata($vacancy);
        self::setAlternateUrls($vacancy);

        View::share('vacancy', $vacancy);
        View::share('model', $vacancy);
        request()->attributes->set('dashed.current_visitable', $vacancy);
        View::share('breadcrumbs', $vacancy->breadcrumbs());
        View::share('page', $page ?: $vacancy);

        return view(config('dashed-core.site_theme', 'dashed') . '.vacancies.show');
    }

    private static function setSeoMetadata(Vacancy $vacancy): void
    {
        $defaultMetadata = [
            'metaTitle' => $vacancy->metadata->title ?? $vacancy->name,
            'metaDescription' => $vacancy->metadata && $vacancy->metadata->description ? $vacancy->metadata->description : $vacancy->excerpt,
            'ogType' => 'article',
            'metaImage' => $vacancy->metadata && $vacancy->metadata->image ? $vacancy->metadata->image : $vacancy->image,
        ];

        foreach ($defaultMetadata as $key => $value) {
            seo()->metaData($key, $value);
        }
    }

    private static function setAlternateUrls(Vacancy $vacancy): void
    {
        $currentLocale = App::getLocale();
        $alternateUrls = [];

        foreach (Sites::getLocales() as $locale) {
            if ($locale['id'] !== $currentLocale) {
                LaravelLocalization::setLocale($locale['id']);
                App::setLocale($locale['id']);
                $alternateUrls[$locale['id']] = $vacancy->getUrl();
            }
        }

        LaravelLocalization::setLocale($currentLocale);
        App::setLocale($currentLocale);

        seo()->metaData('alternateUrls', $alternateUrls);
    }

    public function breadcrumbs(): array
    {
        $breadcrumbs = [];

        $homePage = Page::isHome()->publicShowable()->first();
        if ($homePage) {
            $breadcrumbs[] = [
                'name' => $homePage->name,
                'url' => $homePage->getUrl(),
            ];
        }

        $overviewPage = self::getOverviewPage();
        if ($overviewPage) {
            $breadcrumbs[] = [
                'name' => $overviewPage->name,
                'url' => $overviewPage->getUrl(),
            ];
        }

        if ($this->category) {
            $categoryBreadcrumbs = [];
            $category = $this->category;
            $categoryBreadcrumbs[] = [
                'name' => $category->name,
                'url' => $category->getUrl(),
            ];
            while ($category->parent) {
                $category = $category->parent;
                $categoryBreadcrumbs[] = [
                    'name' => $category->name,
                    'url' => $category->getUrl(),
                ];
            }
            if (count($categoryBreadcrumbs)) {
                $categoryBreadcrumbs = array_reverse($categoryBreadcrumbs);
                $breadcrumbs = array_merge($breadcrumbs, $categoryBreadcrumbs);
            }
        }

        $breadcrumbs[] = [
            'name' => $this->name,
            'url' => $this->getUrl(),
        ];

        return $breadcrumbs;
    }

    public function getUrl($activeLocale = null, bool $native = true)
    {
        $originalLocale = app()->getLocale();
        if (! $activeLocale) {
            $activeLocale = $originalLocale;
        }

        $url = '';

        if ($overviewPage = self::getOverviewPage()) {
            $url .= "{$overviewPage->getUrl($activeLocale)}/";
        } else {
            $url .= '/';
        }

        if (Customsetting::get('vacancy_use_category_in_url') && $this->category) {
            $categoriesToAdd = [];
            $parentCategory = $this->category;
            while ($parentCategory) {
                $categoriesToAdd[] = $parentCategory->getTranslation('slug', $activeLocale);
                $parentCategory = $parentCategory->parent;
            }
            $categoriesToAdd = array_reverse($categoriesToAdd);
            foreach ($categoriesToAdd as $categorySlug) {
                $url .= "{$categorySlug}/";
            }
        }

        $url .= $this->getTranslation('slug', $activeLocale);

        if (! str($url)->startsWith('/')) {
            $url = '/' . $url;
        }

        if ($activeLocale != Locales::getFirstLocale()['id'] && ! str($url)->startsWith("/{$activeLocale}")) {
            $url = '/' . $activeLocale . $url;
        }

        return $native ? $url : url($url);
    }

    public function getJobLocationTypeLabelAttribute(): ?string
    {
        return match ($this->job_location_type) {
            'on-site' => 'Op locatie',
            'hybrid' => 'Hybride',
            'TELECOMMUTE' => 'Op afstand',
            default => null,
        };
    }

    public function getEmploymentTypesLabelAttribute(): string
    {
        $labels = [
            'FULL_TIME' => 'Fulltime',
            'PART_TIME' => 'Parttime',
            'CONTRACTOR' => 'Contractor',
            'TEMPORARY' => 'Tijdelijk',
            'INTERN' => 'Stage',
            'VOLUNTEER' => 'Vrijwilliger',
            'PER_DIEM' => 'Oproepbasis',
            'OTHER' => 'Anders',
        ];

        $values = collect($this->employment_types ?? [])
            ->map(fn ($value) => $labels[$value] ?? $value)
            ->all();

        return implode(', ', $values);
    }

    public function getSalaryDisplayAttribute(): ?string
    {
        if (! $this->salary_min && ! $this->salary_max) {
            return null;
        }

        $currency = $this->salary_currency ?: 'EUR';
        $unitLabels = [
            'HOUR' => 'per uur',
            'DAY' => 'per dag',
            'WEEK' => 'per week',
            'MONTH' => 'per maand',
            'YEAR' => 'per jaar',
        ];
        $unit = $unitLabels[$this->salary_unit_text] ?? '';

        if ($this->salary_min && $this->salary_max && $this->salary_min != $this->salary_max) {
            return trim("{$currency} " . number_format($this->salary_min, 0, ',', '.') . ' - ' . number_format($this->salary_max, 0, ',', '.') . " {$unit}");
        }

        $value = $this->salary_max ?: $this->salary_min;

        return trim("{$currency} " . number_format($value, 0, ',', '.') . " {$unit}");
    }
}
