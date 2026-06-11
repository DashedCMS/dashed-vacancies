@php
    /**
     * Schema.org JobPosting markup for a vacancy.
     * Override per site by publishing dashed-templates and editing
     * resources/views/components/vacancies/schema.blade.php.
     *
     * https://developers.google.com/search/docs/appearance/structured-data/job-posting
     */
    $vacancy = $vacancy ?? null;

    if (! $vacancy) {
        return;
    }

    $orgName = $vacancy->hiring_organization_name
        ?: \Dashed\DashedCore\Models\Customsetting::get('site_name', null, config('app.name'));
    $orgUrl = $vacancy->hiring_organization_url ?: url('/');
    $orgLogo = $vacancy->hiring_organization_logo ?: null;

    $description = trim(strip_tags($vacancy->description ?: $vacancy->excerpt ?: ''));

    $datePosted = optional($vacancy->start_date ?: $vacancy->created_at)->toIso8601String();
    $validThrough = optional($vacancy->valid_through ?: $vacancy->application_deadline ?: $vacancy->end_date)->toIso8601String();

    $jsonLd = [
        '@context' => 'https://schema.org/',
        '@type' => 'JobPosting',
        'title' => (string) $vacancy->name,
        'description' => $description,
        'datePosted' => $datePosted,
        'identifier' => [
            '@type' => 'PropertyValue',
            'name' => $orgName,
            'value' => $vacancy->identifier_value ?: (string) $vacancy->id,
        ],
        'hiringOrganization' => array_filter([
            '@type' => 'Organization',
            'name' => $orgName,
            'sameAs' => $orgUrl,
            'logo' => $orgLogo,
        ]),
        'url' => $vacancy->getUrl(null, false),
    ];

    if ($validThrough) {
        $jsonLd['validThrough'] = $validThrough;
    }

    if ($vacancy->employment_types) {
        $jsonLd['employmentType'] = count($vacancy->employment_types) === 1
            ? $vacancy->employment_types[0]
            : $vacancy->employment_types;
    }

    if ($vacancy->job_location_type === 'TELECOMMUTE') {
        $jsonLd['jobLocationType'] = 'TELECOMMUTE';

        if ($vacancy->applicant_location_requirements) {
            $countries = collect(explode(',', $vacancy->applicant_location_requirements))
                ->map(fn ($code) => ['@type' => 'Country', 'name' => trim($code)])
                ->filter(fn ($c) => $c['name'] !== '')
                ->values()
                ->all();
            if (count($countries)) {
                $jsonLd['applicantLocationRequirements'] = count($countries) === 1 ? $countries[0] : $countries;
            }
        }
    }

    if ($vacancy->city || $vacancy->street_address || $vacancy->country) {
        $jsonLd['jobLocation'] = [
            '@type' => 'Place',
            'address' => array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $vacancy->street_address,
                'addressLocality' => $vacancy->city,
                'addressRegion' => $vacancy->region,
                'postalCode' => $vacancy->postal_code,
                'addressCountry' => $vacancy->country,
            ]),
        ];
    }

    if ($vacancy->salary_min || $vacancy->salary_max) {
        $value = [];
        if ($vacancy->salary_min) {
            $value['minValue'] = (float) $vacancy->salary_min;
        }
        if ($vacancy->salary_max) {
            $value['maxValue'] = (float) $vacancy->salary_max;
        }
        if (! $vacancy->salary_min && $vacancy->salary_max) {
            $value['value'] = (float) $vacancy->salary_max;
        }
        if ($vacancy->salary_min && ! $vacancy->salary_max) {
            $value['value'] = (float) $vacancy->salary_min;
        }
        $value['@type'] = 'QuantitativeValue';
        if ($vacancy->salary_unit_text) {
            $value['unitText'] = $vacancy->salary_unit_text;
        }

        $jsonLd['baseSalary'] = [
            '@type' => 'MonetaryAmount',
            'currency' => $vacancy->salary_currency ?: 'EUR',
            'value' => $value,
        ];
    }

    if ($vacancy->education_requirements) {
        $jsonLd['educationRequirements'] = [
            '@type' => 'EducationalOccupationalCredential',
            'credentialCategory' => (string) $vacancy->education_requirements,
        ];
    }

    if ($vacancy->experience_requirements) {
        $jsonLd['experienceRequirements'] = [
            '@type' => 'OccupationalExperienceRequirements',
            'monthsOfExperience' => is_numeric($vacancy->experience_requirements) ? (int) $vacancy->experience_requirements : null,
            'description' => (string) $vacancy->experience_requirements,
        ];
    }

    if ($vacancy->industry) {
        $jsonLd['industry'] = (string) $vacancy->industry;
    }

    if ($vacancy->skills) {
        $jsonLd['skills'] = (string) $vacancy->skills;
    }

    if ($vacancy->qualifications) {
        $jsonLd['qualifications'] = (string) $vacancy->qualifications;
    }

    if ($vacancy->responsibilities) {
        $jsonLd['responsibilities'] = (string) $vacancy->responsibilities;
    }

    if ($vacancy->benefits) {
        $jsonLd['jobBenefits'] = (string) $vacancy->benefits;
    }

    if ($vacancy->work_hours_min || $vacancy->work_hours_max) {
        $jsonLd['workHours'] = trim(($vacancy->work_hours_min ?? '') . ($vacancy->work_hours_max ? '-' . $vacancy->work_hours_max : '') . ' uur per week');
    }

    if ($vacancy->direct_apply) {
        $jsonLd['directApply'] = true;
    }
@endphp

<script type="application/ld+json">
    {!! json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT) !!}
</script>
