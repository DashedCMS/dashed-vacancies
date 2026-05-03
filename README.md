# Dashed Vacancies

Vacancy module for Dashed CMS. Provides a complete vacancy / job posting module with schema.org JobPosting markup, an optional dashed-forms application form, three content blocks, and a default overview page.

## Installation

This package is consumed via composer path repository inside the dashed-cms monorepo.

```bash
composer require dashed/dashed-vacancies
php artisan migrate
php artisan vendor:publish --tag=dashed-templates
php artisan dashed:create-default-pages
```

## What you get

- **Models** - `Vacancy`, `VacancyCategory` (translatable, soft-deleted, visitable, blockable).
- **Filament admin** - full CRUD with structured form sections for content, job details, location, salary, application, employer overrides, publishing and SEO.
- **Form attachment** - link an existing `dashed-forms` form or auto-generate one.
- **Blocks** - `all-vacancies`, `few-vacancies`, `single-vacancy`.
- **Default pages** - overview page is auto-seeded on `dashed:create-default-pages`.
- **schema.org JobPosting** - JSON-LD rendered from an overridable blade partial.
- **URL routing** - `/vacatures/{slug}` (and optionally `/vacatures/{category}/{slug}`).

## Schema.org overrides

The schema partial lives at `resources/views/components/vacancies/schema.blade.php` after publishing the `dashed-templates` tag. Edit it per site to adjust the JobPosting markup.

## Compatibility

Recommended: `dashed-core` v4.2+ for the full admin experience (navigation group sorting, resource docs, settings docs, role permissions auto-registration).

Older dashed-core versions are also supported - the package guards every "newer" CMS API call (`registerNavigationGroup`, `registerResourceDocs`, `registerSettingsDocs`, `registerRolePermissions`, `registerRouteModel`, `registerSettingsPage`) with `method_exists`, so unsupported features simply degrade silently. The vacancy models, builder blocks, default page seeding, schema.org markup and frontend templates work on every dashed-core version.

The application form upload field requires `dashed-forms` v4.0.23+ (which ships the `form-components/file.blade.php` view). Use a text input for CV/portfolio links if you stay on an older dashed-forms.
