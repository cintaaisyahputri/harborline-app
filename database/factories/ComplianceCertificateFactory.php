<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ComplianceCertificateFactory extends Factory
{
    public function definition(): array
    {
        $issued = $this->faker->dateTimeBetween('-2 years', '-1 month');

        return [
            'certificate_type' => $this->faker->randomElement([
                'health_inspection', 'catch_origin', 'export_license', 'safety_survey',
            ]),
            'certificate_number' => strtoupper('CERT-' . $this->faker->unique()->bothify('##??####')),
            'issuing_authority' => $this->faker->randomElement([
                'Ministry of Marine Affairs and Fisheries', 'Port Health Authority', 'Fisheries Export Board',
            ]),
            'issued_at' => $issued,
            'expires_at' => $this->faker->dateTimeBetween($issued, '+2 years'),
            'document_url' => null,
        ];
    }
}
