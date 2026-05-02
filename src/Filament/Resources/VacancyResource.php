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
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Dashed\DashedVacancies\Models\Vacancy;
use Filament\Forms\Components\CheckboxList;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Dashed\DashedCore\Classes\QueryHelpers\SearchQuery;
use Dashed\DashedCore\Filament\Concerns\HasVisitableTab;
use Dashed\DashedCore\Filament\Concerns\HasCustomBlocksTab;
use LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable;
use Dashed\DashedCore\Classes\Actions\ActionGroups\ToolbarActions;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\EditVacancy;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\ListVacancies;
use Dashed\DashedVacancies\Filament\Resources\VacancyResource\Pages\CreateVacancy;

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
                Section::make('Content')->columnSpanFull()
                    ->schema(array_merge([
                        TextInput::make('name')
                            ->label('Naam')
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
                            ->label('Slug')
                            ->unique('dashed__vacancies', 'slug', fn ($record) => $record)
                            ->helperText('Laat leeg om automatisch te laten genereren')
                            ->maxLength(255),
                        Select::make('category_id')
                            ->label('Categorie')
                            ->nullable()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                            ->relationship('category', 'name'),
                        Textarea::make('excerpt')
                            ->label('Korte tekst'),
                        Textarea::make('description')
                            ->label('Volledige omschrijving')
                            ->helperText('Wordt gebruikt voor de schema.org JobPosting description'),
                        mediaHelper()->field('image', 'Hoofd afbeelding', isImage: true)
                            ->helperText('Wordt gebruikt op de overzichtspagina'),
                        cms()->getFilamentBuilderBlock(),
                    ], static::customBlocksTab('vacancyBlocks')))
                    ->columns(2),

                Section::make('Functie details')->columnSpanFull()
                    ->schema([
                        Textarea::make('responsibilities')->label('Verantwoordelijkheden')->rows(4),
                        Textarea::make('requirements')->label('Vereisten')->rows(4),
                        Textarea::make('benefits')->label('Wat bieden wij')->rows(4),
                        Textarea::make('qualifications')->label('Kwalificaties')->rows(3),
                        TextInput::make('skills')->label('Skills (komma gescheiden)'),
                        TextInput::make('education_requirements')->label('Opleidingsniveau'),
                        TextInput::make('experience_requirements')->label('Ervaring'),
                        TextInput::make('industry')->label('Branche'),
                        Select::make('experience_level')
                            ->label('Ervaringsniveau')
                            ->options([
                                'entry' => 'Starter',
                                'mid' => 'Medior',
                                'senior' => 'Senior',
                                'lead' => 'Lead',
                            ])
                            ->nullable(),
                        CheckboxList::make('employment_types')
                            ->label('Dienstverband')
                            ->options([
                                'FULL_TIME' => 'Fulltime',
                                'PART_TIME' => 'Parttime',
                                'CONTRACTOR' => 'Contractor',
                                'TEMPORARY' => 'Tijdelijk',
                                'INTERN' => 'Stage',
                                'VOLUNTEER' => 'Vrijwilliger',
                                'PER_DIEM' => 'Oproepbasis',
                                'OTHER' => 'Anders',
                            ])
                            ->columns(2),
                        TextInput::make('work_hours_min')->label('Uren per week vanaf')->numeric()->nullable(),
                        TextInput::make('work_hours_max')->label('Uren per week tot')->numeric()->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Locatie')->columnSpanFull()
                    ->schema([
                        Select::make('job_location_type')
                            ->label('Werkvorm')
                            ->options([
                                'on-site' => 'Op locatie',
                                'hybrid' => 'Hybride',
                                'TELECOMMUTE' => 'Op afstand (remote)',
                            ])
                            ->nullable(),
                        TextInput::make('street_address')->label('Straat + nr')->nullable(),
                        TextInput::make('postal_code')->label('Postcode')->nullable(),
                        TextInput::make('city')->label('Plaats')->nullable(),
                        TextInput::make('region')->label('Regio / provincie')->nullable(),
                        TextInput::make('country')->label('Land (ISO bv. NL)')->nullable()->maxLength(2),
                        TextInput::make('applicant_location_requirements')
                            ->label('Toegestane landen voor remote (komma gescheiden ISO codes)')
                            ->helperText('Alleen invullen bij volledig remote vacatures, bv. NL,BE,DE')
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Salaris')->columnSpanFull()
                    ->schema([
                        TextInput::make('salary_min')->label('Salaris vanaf')->numeric()->nullable(),
                        TextInput::make('salary_max')->label('Salaris tot')->numeric()->nullable(),
                        Select::make('salary_currency')
                            ->label('Valuta')
                            ->options([
                                'EUR' => 'EUR',
                                'USD' => 'USD',
                                'GBP' => 'GBP',
                            ])
                            ->default('EUR')
                            ->nullable(),
                        Select::make('salary_unit_text')
                            ->label('Per')
                            ->options([
                                'HOUR' => 'Per uur',
                                'DAY' => 'Per dag',
                                'WEEK' => 'Per week',
                                'MONTH' => 'Per maand',
                                'YEAR' => 'Per jaar',
                            ])
                            ->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Solliciteren')->columnSpanFull()
                    ->schema([
                        Toggle::make('direct_apply')
                            ->label('Sollicitatie via deze pagina')
                            ->reactive()
                            ->default(true),
                        Select::make('form_id')
                            ->label('Sollicitatieformulier')
                            ->helperText('Selecteer een formulier of laat leeg om automatisch een formulier te genereren bij opslaan')
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
                        Toggle::make('auto_create_form')
                            ->label('Automatisch een sollicitatieformulier aanmaken')
                            ->dehydrated(false)
                            ->helperText('Maakt een standaard sollicitatieformulier aan en koppelt deze automatisch aan deze vacature')
                            ->visible(fn (Get $get) => (bool) $get('direct_apply') && ! $get('form_id')),
                        TextInput::make('application_url')
                            ->label('Externe sollicitatie URL')
                            ->url()
                            ->nullable()
                            ->visible(fn (Get $get) => ! $get('direct_apply')),
                        TextInput::make('application_email')
                            ->label('Sollicitatie e-mailadres')
                            ->email()
                            ->nullable()
                            ->visible(fn (Get $get) => ! $get('direct_apply')),
                        DateTimePicker::make('application_deadline')->label('Sluitingsdatum sollicitaties')->nullable(),
                        DatePicker::make('valid_through')->label('Geldig tot (schema.org)')->nullable(),
                        TextInput::make('identifier_value')->label('Vacature referentie / ID')->nullable(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Werkgever (optioneel)')->columnSpanFull()
                    ->schema([
                        TextInput::make('hiring_organization_name')->label('Naam')->nullable(),
                        TextInput::make('hiring_organization_url')->label('Website')->url()->nullable(),
                        TextInput::make('hiring_organization_logo')->label('Logo URL')->nullable(),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),

                Section::make('Globale informatie')->columnSpanFull()
                    ->schema(static::publishTab())
                    ->collapsed(fn ($livewire) => $livewire instanceof EditVacancy),
                Section::make('Meta data')->columnSpanFull()
                    ->schema(static::metadataTab()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(array_merge([
                TextColumn::make('name')
                    ->label('Naam')
                    ->sortable()
                    ->searchable(query: SearchQuery::make()),
                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Plaats')
                    ->toggleable(),
                TextColumn::make('employment_types_label')
                    ->label('Dienstverband')
                    ->toggleable(),
            ], static::visitableTableColumns()))
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('category')
                    ->label('Categorie')
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
