# Changelog

All notable changes to `dashed-vacancies` will be documented in this file.

## v4.1.0 - 2026-05-02

### Changed
- Replaced the in-form `auto_create_form` toggle with a proper "Sollicitatieformulier aanmaken" header action button on the vacancy edit page. The button is only visible when no form is yet attached, opens a confirmation modal, and creates + links the form in one click.
- Removed the `mutateFormDataBeforeCreate` / `mutateFormDataBeforeSave` auto-create hooks — the action is now the single, explicit entry point.

## v4.0.0 - 2026-05-02

Initial release.

- Vacancy + VacancyCategory models with `IsVisitable`, `HasCustomBlocks`, `SoftDeletes`.
- Migration uses `Migrations::createTableForVisitableModel()` helper for the standard visitable fields (name, slug, content, parent_id, site_ids, start_date, end_date, public, order, timestamps, soft deletes).
- All vacancy fields are optional except name and slug. Includes schema.org JobPosting-aligned fields: description, responsibilities, requirements, benefits, qualifications, skills, education/experience requirements, industry, employment_types, work_hours, salary range with currency and unit, location (city/region/country/postal code), job_location_type (on-site/hybrid/TELECOMMUTE), applicant_location_requirements, valid_through, application_deadline, direct_apply, application_url/email, identifier, hiring organisation overrides.
- Filament `VacancyResource` with structured form sections (content, functie details, locatie, salaris, solliciteren, werkgever, publish, SEO).
- Filament `VacancyCategoryResource` with nested categories.
- Form attachment: select an existing `dashed-forms` form OR toggle `auto_create_form` to auto-generate a sollicitatieformulier (Naam, Email, Telefoon, LinkedIn, Motivatie, CV) via `VacancyForms::createApplicationForm()`.
- Three builder blocks: `all-vacancies`, `few-vacancies`, `single-vacancy`.
- Default page seeding: `createDefaultPages()` creates a Vacatures overview page with the `all-vacancies` block.
- Schema.org JobPosting JSON-LD rendered via overridable blade partial `components/vacancies/schema.blade.php` (publishable per site).
- Publishable templates under tag `dashed-templates` covering single view, listing view, category view, vacancy card, sidebar, apply box, schema partial, and all three blocks.
- `VacanciesSettingsPage` for per-site overview page + URL toggle.
- `Vacancies` helper class for fetching vacancies on the frontend.
- Policies: `VacancyPolicy`, `VacancyCategoryPolicy`. Role permissions registered.
