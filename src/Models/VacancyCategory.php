<?php

namespace Dashed\DashedVacancies\Models;

use Dashed\DashedPages\Models\Page;
use Illuminate\Support\Facades\App;
use Dashed\DashedCore\Classes\Locales;
use Illuminate\Database\Eloquent\Model;
use Dashed\DashedCore\Models\Customsetting;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dashed\DashedCore\Models\Concerns\IsVisitable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Dashed\DashedCore\Models\Concerns\HasCustomBlocks;

class VacancyCategory extends Model
{
    use HasCustomBlocks;
    use IsVisitable;
    use SoftDeletes;

    protected $table = 'dashed__vacancy_categories';

    public $translatable = [
        'name',
        'slug',
        'content',
    ];

    protected $casts = [
        'site_ids' => 'array',
        'content' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'public' => 'boolean',
    ];

    protected $appends = [
        'status',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(Vacancy::class, 'category_id');
    }

    public function allChildIds(): array
    {
        $ids = [];
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $child->allChildIds());
        }

        return $ids;
    }

    public static function canHaveParent(): bool
    {
        return true;
    }

    public static function resolveRoute($parameters = [])
    {
        $slug = $parameters['slug'] ?? '';
        if (empty($slug)) {
            return;
        }

        $slugComponents = explode('/', $slug);
        $lastSlugPart = $slugComponents[array_key_last($slugComponents)] ?? null;

        $category = self::publicShowable()
            ->where('slug->' . App::getLocale(), $lastSlugPart)
            ->first();

        if (! $category) {
            return;
        }

        if (! \Illuminate\Support\Facades\View::exists(config('dashed-core.site_theme', 'dashed') . '.vacancy-category.show')) {
            return 'pageNotFound';
        }

        seo()->metaData('metaTitle', $category->metadata->title ?? $category->name);
        seo()->metaData('metaDescription', $category->metadata->description ?? '');

        \Illuminate\Support\Facades\View::share('category', $category);
        \Illuminate\Support\Facades\View::share('model', $category);
        \Illuminate\Support\Facades\View::share('breadcrumbs', $category->breadcrumbs());
        \Illuminate\Support\Facades\View::share('page', $category);
        request()->attributes->set('dashed.current_visitable', $category);

        return view(config('dashed-core.site_theme', 'dashed') . '.vacancy-category.show');
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

        $overviewPage = Vacancy::getOverviewPage();
        if ($overviewPage) {
            $breadcrumbs[] = [
                'name' => $overviewPage->name,
                'url' => $overviewPage->getUrl(),
            ];
        }

        $chain = [];
        $node = $this;
        while ($node) {
            $chain[] = [
                'name' => $node->name,
                'url' => $node->getUrl(),
            ];
            $node = $node->parent;
        }

        return array_merge($breadcrumbs, array_reverse($chain));
    }

    public function getUrl($activeLocale = null, bool $native = true)
    {
        if (! $activeLocale) {
            $activeLocale = app()->getLocale();
        }

        $overviewPageId = Customsetting::get('vacancy_category_overview_page_id');
        if ($overviewPageId) {
            $overview = Page::find($overviewPageId);
            $base = $overview ? $overview->getUrl($activeLocale) : '';
        } else {
            $vacancyOverview = Vacancy::getOverviewPage();
            $base = $vacancyOverview ? $vacancyOverview->getUrl($activeLocale) : '';
        }

        $url = $base ? rtrim($base, '/') . '/' : '/';
        $url .= $this->getTranslation('slug', $activeLocale);

        if (! str($url)->startsWith('/')) {
            $url = '/' . $url;
        }

        if ($activeLocale != Locales::getFirstLocale()['id'] && ! str($url)->startsWith("/{$activeLocale}")) {
            $url = '/' . $activeLocale . $url;
        }

        return $native ? $url : url($url);
    }
}
