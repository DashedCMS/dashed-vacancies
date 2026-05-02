<?php

namespace Dashed\DashedVacancies\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Dashed\DashedVacancies\Models\Vacancy;
use Dashed\DashedVacancies\Models\VacancyCategory;

class ShowVacancies extends Component
{
    use WithPagination;

    public int $pagination = 12;

    public ?int $category = null;
    public ?array $categoryIds = [];

    public ?string $search = null;
    public ?string $employmentType = null;
    public ?string $jobLocationType = null;

    public string $sort = 'latest';
    public array $blockData = [];

    public function mount(
        int $pagination = 12,
        ?int $category = null,
        ?string $search = null,
        ?string $employmentType = null,
        ?string $jobLocationType = null,
        string $sort = 'latest',
        array $blockData = []
    ) {
        $this->pagination = $pagination;
        $this->category = $category;
        if ($category) {
            $categoryIds = [$category];
            $categoryModel = VacancyCategory::find($category);
            if ($categoryModel) {
                $categoryIds = array_merge($categoryIds, $categoryModel->allChildIds());
            }
            $this->categoryIds = $categoryIds;
        }
        $this->search = $search;
        $this->employmentType = $employmentType;
        $this->jobLocationType = $jobLocationType;
        $this->sort = $sort;
        $this->blockData = $blockData;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $categoryIds = $this->categoryIds;
        $search = $this->search;
        $employmentType = $this->employmentType;
        $jobLocationType = $this->jobLocationType;
        $sort = $this->sort;
        $pagination = $this->pagination;

        return view(config('dashed-core.site_theme', 'dashed') . '.vacancies.show-vacancies', [
            'vacancies' => Vacancy::query()
                ->thisSite()
                ->publicShowable()
                ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
                ->when($search, function ($q, $search) {
                    return $q->where(function ($inner) use ($search) {
                        $inner->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('city', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
                    });
                })
                ->when($employmentType, fn ($q, $type) => $q->whereJsonContains('employment_types', $type))
                ->when($jobLocationType, fn ($q, $type) => $q->where('job_location_type', $type))
                ->when($sort === 'latest', fn ($q) => $q->latest())
                ->when($sort === 'oldest', fn ($q) => $q->oldest())
                ->paginate($pagination),
        ]);
    }
}
