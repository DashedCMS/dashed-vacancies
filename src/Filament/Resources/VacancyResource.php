<?php

namespace Dashed\DashedVacancies\Filament\Resources;

use UnitEnum;
use BackedEnum;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Dashed\DashedVacancies\Models\Vacancy;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dashed\DashedCore\Classes\QueryHelpers\SearchQuery;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Dashed\DashedCore\Classes\Actions\ActionGroups\ToolbarActions;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\EditVacancy;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\CreateVacancy;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\ListVacancies;

class VacancyResource extends Resource
{
    use HasCustomBlocksTab;
    use HasVisitableTab;
    use Translatable;

    protected static ?string $model = Vacancy::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-briefcase';

    protected static string | UnitEnum | null $navigationGroup = 'Vacatures';

    protected static ?string $navigationLabel = 'Vacatures';

    protected static ?string $label = 'Vacature';

    protected static ?string $pluralLabel = 'Vacatures';

    protected static ?int $navigationSort = 5;

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
            'slug',
            'category.name',
            'content',
            'description',
            'city',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make(__('Content'))->columnSpanFull()
                    ->schema(array_merge([
                        TextInput::make('name')
                            ->label(__('Naam'))
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->lazy()
                            ->afterStateUpdated(function (Set $set, $state, $livewire) {
                                if ($livewire instanceof CreateVacancy) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->label(__('Slug'))
                            ->unique('dashed__vacancies', 'slug', fn ($record) => $record)
                            ->helperText(__('Laat leeg om automatisch te laten genereren'))
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label(__('Categorie'))
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->relationship('category', 'name'),
                        Textarea::make('excerpt')
                            ->label(__('Korte tekst')),
                        Textarea::make('description')
                            ->label(__('Volledige omschrijving'))
                            ->helperText(__('Wordt gebruikt voor de schema.org JobPosting description')),
                        mediaHelper()->field('image', 'Hoofd afbeelding', isImage: true)
                            ->helperText(__('Wordt gebruikt op de overzichtspagina')),
                        cms()->getFilamentBuilderBlock(),
                    ], static::customBlocksTab('vacancyBlocks')))
                    ->columns(2),

                Section::make(__('Functie details'))->columnSpanFull()
                    ->schema([
                        Textarea::make('responsibilities')->label(__('Verantwoordelijkheden'))->rows(4),
                        Textarea::make('requirements')->label(__('Vereisten'))->rows(4),
                        Textarea::make('benefits')->label(__('Wat bieden wij'))->rows(4),
                        Textarea::make('qualifications')->label(__('Kwalificaties'))->rows(3),
                        TextInput::make('skills')->label(__('Skills (komma gescheiden)')),
                        TextInput::make('education_requirements')->label(__('Opleidingsniveau')),
                        TextInput::make('experience_requirements')->label(__('Ervaring')),
                        TextInput::make('industry')->label(__('Branche')),
                        Select::make('experience_level')
                            ->label(__('Ervaringsniveau'))
                            ->options([
                                'entry' => __('Starter'),
                                'mid' => __('Medior'),
                                'senior' => __('Senior'),
                                'lead' => __('Lead'),
                            ])
                            ->nullable(),
                        CheckboxList::make('employment_types')
                            ->label(__('Dienstverband'))
                            ->options([
                                'FULL_TIME' => __('Fulltime'),
                                'PART_TIME' => __('Parttime'),
                                'CONTRACTOR' => __('Contractor'),
                                'TEMPORARY' => __('Tijdelijk'),
                                'INTERN' => __('Stage'),
                                'VOLUNTEER' => __('Vrijwilliger'),
                                'PER_DIEM' => __('Oproepbasis'),
                                'OTHER' => __('Anders'),
                            ])
                            ->columns(2),
                        TextInput::make('work_hours_min')->label(__('Uren per week vanaf'))->numeric()->nullable(),
                        TextInput::make('work_hours_max')->label(__('Uren per week tot'))->numeric()->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('Locatie'))->columnSpanFull()
                    ->schema([
                        Select::make('job_location_type')
                            ->label(__('Werkvorm'))
                            ->options([
                                'on-site' => __('Op locatie'),
                                'hybrid' => __('Hybride'),
                                'TELECOMMUTE' => __('Op afstand (remote)'),
                            ])
                            ->nullable(),
                        TextInput::make('street_address')->label(__('Straat + nr'))->nullable(),
                        TextInput::make('postal_code')->label(__('Postcode'))->nullable(),
                        TextInput::make('city')->label(__('Plaats'))->nullable(),
                        TextInput::make('region')->label(__('Regio / provincie'))->nullable(),
                        TextInput::make('country')->label(__('Land (ISO bv. NL)'))->nullable()->maxLength(2),
                        TextInput::make('applicant_location_requirements')
                            ->label(__('Toegestane landen voor remote (komma gescheiden ISO codes)'))
                            ->helperText(__('Alleen invullen bij volledig remote vacatures, bv. NL,BE,DE'))
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('Salaris'))->columnSpanFull()
                    ->schema([
                        TextInput::make('salary_min')->label(__('Salaris vanaf'))->numeric()->nullable(),
                        TextInput::make('salary_max')->label(__('Salaris tot'))->numeric()->nullable(),
                        Select::make('salary_currency')
                            ->label(__('Valuta'))
                            ->options([
                                'EUR' => __('EUR'),
                                'USD' => __('USD'),
                                'GBP' => __('GBP'),
                            ])
                            ->default('EUR')
                            ->nullable(),
                        Select::make('salary_unit_text')
                            ->label(__('Per'))
                            ->options([
                                'HOUR' => __('Per uur'),
                                'DAY' => __('Per dag'),
                                'WEEK' => __('Per week'),
                                'MONTH' => __('Per maand'),
                                'YEAR' => __('Per jaar'),
                            ])
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('Solliciteren'))->columnSpanFull()
                    ->schema([
                        Toggle::make('direct_apply')
                            ->label(__('Sollicitatie via deze pagina'))
                            ->reactive()
                            ->default(true),
                        Select::make('form_id')
                            ->label(__('Sollicitatieformulier'))
                            ->helperText(__('Selecteer een bestaand formulier, of gebruik na het opslaan de knop "Sollicitatieformulier aanmaken" bovenin om er automatisch een te genereren'))
                            ->searchable()
                            ->nullable()
                            ->preload()
                            ->options(function () {
                                if (! class_exists(\Dashed\DashedForms\Models\Form::class)) {
                                    return [];
                                }

                                return \Dashed\DashedForms\Models\Form::query()
                                    ->orderBy('id', 'desc')
                                    ->pluck('name', 'id')
                                    ->all();
                            })
                            ->visible(fn (Get $get) => (bool) $get('direct_apply')),
                        TextInput::make('application_url')
                            ->label(__('Externe sollicitatie URL'))
                            ->url()
                            ->nullable()
                            ->visible(fn (Get $get) => ! $get('direct_apply')),
                        TextInput::make('application_email')
                            ->label(__('Sollicitatie e-mailadres'))
                            ->email()
                            ->nullable()
                            ->visible(fn (Get $get) => ! $get('direct_apply')),
                        DateTimePicker::make('application_deadline')->label(__('Sluitingsdatum sollicitaties'))->nullable(),
                        DatePicker::make('valid_through')->label(__('Geldig tot (schema.org)'))->nullable(),
                        TextInput::make('identifier_value')->label(__('Vacature referentie / ID'))->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make(__('Werkgever (optioneel)'))->columnSpanFull()
                    ->schema([
                        TextInput::make('hiring_organization_name')->label(__('Naam'))->nullable(),
                        TextInput::make('hiring_organization_url')->label(__('Website'))->url()->nullable(),
                        TextInput::make('hiring_organization_logo')->label(__('Logo URL'))->nullable(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Section::make(__('Globale informatie'))->columnSpanFull()
                    ->schema(static::publishTab())
                    ->collapsed(fn ($livewire) => $livewire instanceof EditVacancy),
                Section::make(__('Meta data'))->columnSpanFull()
                    ->schema(static::metadataTab()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(array_merge([
                TextColumn::make('name')
                    ->label(__('Naam'))
                    ->sortable()
                    ->searchable(query: SearchQuery::make()),
                TextColumn::make('category.name')
                    ->label(__('Categorie'))
                    ->sortable(),
                TextColumn::make('city')
                    ->label(__('Plaats'))
                    ->toggleable(),
                TextColumn::make('employment_types_label')
                    ->label(__('Dienstverband'))
                    ->toggleable(),
            ], static::visitableTableColumns()))
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category')
                    ->label(__('Categorie'))
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('category', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->reorderable('order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions(ToolbarActions::getActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVacancies::route('/'),
            'create' => CreateVacancy::route('/create'),
            'edit' => EditVacancy::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
