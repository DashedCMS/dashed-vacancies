<?php

namespace Dashed\DashedVacancies\Filament\Pages\Settings;

use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Dashed\DashedCore\Classes\Sites;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Dashed\DashedCore\Models\Customsetting;
use Dashed\DashedPages\Models\Page as PageModel;
use Dashed\DashedCore\Traits\HasSettingsPermission;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class VacanciesSettingsPage extends Page implements HasSchemas
{
    use InteractsWithSchemas;
    use HasSettingsPermission;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Vacatures';

    protected string $view = 'dashed-core::settings.pages.default-settings';

    public array $data = [];

    public function mount(): void
    {
        $formData = [];
        foreach (Sites::getSites() as $site) {
            $formData["vacancy_overview_page_id_{$site['id']}"] = Customsetting::get('vacancy_overview_page_id', $site['id']);
            $formData["vacancy_category_overview_page_id_{$site['id']}"] = Customsetting::get('vacancy_category_overview_page_id', $site['id']);
            $formData["vacancy_use_category_in_url_{$site['id']}"] = Customsetting::get('vacancy_use_category_in_url', $site['id']);
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $tabs = [];
        foreach (Sites::getSites() as $site) {
            $newSchema = [
                Select::make("vacancy_overview_page_id_{$site['id']}")
                    ->label(__('Vacature overzicht pagina'))
                    ->searchable()
                    ->preload()
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
                Select::make("vacancy_category_overview_page_id_{$site['id']}")
                    ->label(__('Vacature categorie overzicht pagina'))
                    ->searchable()
                    ->preload()
                    ->options(PageModel::thisSite($site['id'])->pluck('name', 'id')),
                Toggle::make("vacancy_use_category_in_url_{$site['id']}")
                    ->label(__('Gebruik categorie in url')),
            ];

            $tabs[] = Tab::make($site['id'])
                ->label(ucfirst($site['name']))
                ->schema($newSchema)
                ->columns(['default' => 1, 'lg' => 2]);
        }

        return $schema
            ->schema([Tabs::make('Sites')->tabs($tabs)])
            ->statePath('data');
    }

    public function submit()
    {
        foreach (Sites::getSites() as $site) {
            Customsetting::set('vacancy_overview_page_id', $this->form->getState()["vacancy_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('vacancy_category_overview_page_id', $this->form->getState()["vacancy_category_overview_page_id_{$site['id']}"], $site['id']);
            Customsetting::set('vacancy_use_category_in_url', $this->form->getState()["vacancy_use_category_in_url_{$site['id']}"], $site['id']);
        }

        Notification::make()
            ->title(__('De vacature instellingen zijn opgeslagen'))
            ->success()
            ->send();

        return redirect(VacanciesSettingsPage::getUrl());
    }
}
