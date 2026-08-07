<?php

namespace Dashed\DashedVacancies;

use Livewire\Livewire;
use Illuminate\Support\Facades\Gate;
use App\Providers\AppServiceProvider;
use Filament\Forms\Components\Select;
use Dashed\DashedCore\Classes\Locales;
use Spatie\LaravelPackageTools\Package;
use Filament\Forms\Components\TextInput;
use Dashed\DashedVacancies\Models\Vacancy;
use Filament\Forms\Components\Builder\Block;
use Dashed\DashedVacancies\Livewire\ShowVacancies;
use Dashed\DashedVacancies\Models\VacancyCategory;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Dashed\DashedVacancies\Filament\Pages\Settings\VacanciesSettingsPage;

class DashedVacanciesServiceProvider extends PackageServiceProvider
{
    public static string $name = 'dashed-vacancies';

    public function bootingPackage()
    {
        $cms = cms();

        if (method_exists($cms, 'registerNavigationGroup')) {
            $cms->registerNavigationGroup('Vacatures', 45);
        }

        Livewire::component('vacancies.show-vacancies', ShowVacancies::class);

        $cms->builder('publishOnUpdate', [
            'dashed-vacancies-config',
        ]);

        $cms->builder('createDefaultPages', [
            self::class => 'createDefaultPages',
        ]);

        $cms->builder('plugins', [
            new DashedVacanciesPlugin(),
        ]);

        $cms->builder('blockDisabledForCache', [
            'all-vacancies',
        ]);

        if (method_exists($cms, 'registerResourceDocs')) {
            $cms->registerResourceDocs(
                resource: \Dashed\DashedVacancies\Filament\Resources\VacancyResource::class,
                title: 'Vacatures',
                intro: 'Beheer hier alle vacatures van je organisatie. Per vacature leg je een titel, omschrijving, locatie, dienstverband, salaris en sollicitatieproces vast. Optioneel hang je een sollicitatieformulier aan de vacature of laat je deze automatisch aanmaken.',
                sections: [
                    [
                        'heading' => 'Wat kun je hier doen?',
                        'body' => <<<MARKDOWN
- Nieuwe vacatures aanmaken en bestaande bewerken.
- De volledige functieomschrijving, verantwoordelijkheden, vereisten en voordelen vastleggen.
- Locatie, werkvorm (op locatie / hybride / remote) en salarisindicatie invullen.
- Een sollicitatieformulier koppelen of automatisch laten genereren.
- SEO en schema.org JobPosting metadata wordt automatisch opgebouwd uit de vacature velden.
- Maatwerk blokken toevoegen aan een vacature.
MARKDOWN,
                    ],
                    [
                        'heading' => 'Schema.org JobPosting',
                        'body' => 'Alle relevante velden worden automatisch verwerkt in schema.org JobPosting JSON-LD op de detailpagina. De blade template (`components/vacancies/schema.blade.php`) is publiceerbaar zodat je per site de markup kunt aanpassen.',
                    ],
                ],
                tips: [
                    'Vul "Geldig tot" in zodat de vacature na de deadline automatisch uit zoekresultaten van Google for Jobs verdwijnt.',
                    'Bij een volledig remote vacature zet "Werkvorm" op "Op afstand" en vul de toegestane landen in.',
                    'Laat het systeem automatisch een formulier aanmaken als je nog geen sollicitatieformulier hebt klaar staan.',
                ],
            );

            $cms->registerResourceDocs(
                resource: \Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource::class,
                title: 'Vacature categorieën',
                intro: 'Groepeer vacatures in categorieën. Categorieën kunnen genest worden om bv. teams of afdelingen te modelleren.',
                sections: [
                    [
                        'heading' => 'Wat kun je hier doen?',
                        'body' => "- Nieuwe categorieën aanmaken en hernoemen.\n- Categorieën nesten onder een hoofdcategorie.\n- SEO data per categorie instellen.",
                    ],
                ],
                tips: [
                    'Houd categorienamen kort en herkenbaar.',
                    'Maximaal twee niveaus diep is meestal voldoende.',
                ],
            );
        }

        if (method_exists($cms, 'registerSettingsDocs')) {
            $cms->registerSettingsDocs(
                page: VacanciesSettingsPage::class,
                title: 'Vacature instellingen',
                intro: 'Koppel hier per site de pagina die als vacature overzicht dient en bepaal de URL opbouw.',
                sections: [
                    [
                        'heading' => 'Wat kun je hier instellen?',
                        'body' => 'Per site wijs je een vacature overzicht pagina en optioneel een categorie overzicht pagina aan. Daarnaast kies je of de categorie in de URL van een vacature verschijnt.',
                    ],
                ],
                fields: [
                    'Vacature overzicht pagina' => 'De pagina waar het vacature overzicht toont. Wordt het basisadres voor alle vacature URLs.',
                    'Vacature categorie overzicht pagina' => 'Optionele pagina waarop categorieën worden getoond.',
                    'Gebruik categorie in url' => 'Aan zorgt voor URLs als /vacatures/categorie/vacature-titel; uit zorgt voor /vacatures/vacature-titel.',
                ],
                tips: [
                    'Verander de URL opbouw bij voorkeur niet meer als de site live is - bestaande links gaan dan kapot.',
                ],
            );
        }

        Gate::policy(Vacancy::class, \Dashed\DashedVacancies\Policies\VacancyPolicy::class);
        Gate::policy(VacancyCategory::class, \Dashed\DashedVacancies\Policies\VacancyCategoryPolicy::class);

        if (method_exists($cms, 'registerRolePermissions')) {
            $cms->registerRolePermissions('Vacatures', [
                'view_vacancy' => 'Vacatures bekijken',
                'edit_vacancy' => 'Vacatures bewerken',
                'delete_vacancy' => 'Vacatures verwijderen',
                'view_vacancy_category' => 'Vacature categorieën bekijken',
                'edit_vacancy_category' => 'Vacature categorieën bewerken',
                'delete_vacancy_category' => 'Vacature categorieën verwijderen',
            ]);
        }

        if (method_exists($cms, 'registerContentQualityModel')) {
            $cms->registerContentQualityModel(
                \Dashed\DashedVacancies\Models\Vacancy::class,
                \Dashed\DashedVacancies\Filament\Resources\VacancyResource::class,
                'Vacature'
            );
            $cms->registerContentQualityModel(
                \Dashed\DashedVacancies\Models\VacancyCategory::class,
                \Dashed\DashedVacancies\Filament\Resources\VacancyCategoryResource::class,
                'Vacaturecategorie'
            );
        }
    }

    public static function builderBlocks(): void
    {
        $defaultBlocks = [
            Block::make('all-vacancies')
                ->label('Alle vacatures')
                ->schema([
                    AppServiceProvider::getDefaultBlockFields(),
                    TextInput::make('title')->label('Titel'),
                    TextInput::make('subtitle')->label('Subtitel'),
                ]),
            Block::make('few-vacancies')
                ->label('Paar vacatures')
                ->schema([
                    AppServiceProvider::getDefaultBlockFields(),
                    TextInput::make('title')->label('Titel'),
                    TextInput::make('subtitle')->label('Subtitel'),
                    TextInput::make('amount')
                        ->label('Aantal vacatures')
                        ->numeric()
                        ->default(3),
                ]),
            Block::make('single-vacancy')
                ->label('Een vacature uitlichten')
                ->schema([
                    AppServiceProvider::getDefaultBlockFields(),
                    Select::make('vacancy_id')
                        ->label('Vacature')
                        ->options(fn () => Vacancy::query()->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                ]),
        ];

        cms()->builder('blocks', $defaultBlocks);
    }

    public function configurePackage(Package $package): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../resources/templates' => resource_path('views/' . config('dashed-core.site_theme', 'dashed')),
            __DIR__ . '/../resources/component-templates' => resource_path('views/components'),
        ], 'dashed-templates');

        $cms = cms();

        if (method_exists($cms, 'registerRouteModel')) {
            $cms->registerRouteModel(Vacancy::class, 'Vacature', 'Vacatures');
            $cms->registerRouteModel(VacancyCategory::class, 'Vacature categorie', 'Vacature categorieën');
        }

        if (method_exists($cms, 'registerSettingsPage')) {
            $cms->registerSettingsPage(VacanciesSettingsPage::class, 'Vacature');
        }

        $package
            ->hasConfigFile(['dashed-vacancies'])
            ->name(self::$name);
    }

    public static function createDefaultPages(): void
    {
        if (! \Dashed\DashedCore\Models\Customsetting::get('vacancy_overview_page_id')) {
            $page = new \Dashed\DashedPages\Models\Page();
            foreach (Locales::getActivatedLocalesFromSites() as $locale) {
                $page->setTranslation('name', $locale, 'Vacatures');
                $page->setTranslation('slug', $locale, 'vacatures');
                $page->setTranslation('content', $locale, [
                    [
                        'data' => [
                            'in_container' => true,
                            'top_margin' => true,
                            'bottom_margin' => true,
                        ],
                        'type' => 'all-vacancies',
                    ],
                ]);
            }
            $page->save();

            \Dashed\DashedCore\Models\Customsetting::set('vacancy_overview_page_id', $page->id);
        }
    }
}
