<?php

namespace Database\Factories;

use App\Enums\TipoCertificado;
use App\Models\Certificado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificado>
 */
class CertificadoFactory extends Factory
{
    protected $model = Certificado::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $inicio = fake()->dateTimeBetween('-2 years', '-6 months');
        $fin = fake()->dateTimeBetween($inicio, '-1 month');

        return [
            'codigo' => Certificado::generarCodigo(),
            'tipo' => fake()->randomElement(TipoCertificado::cases())->value,
            'beneficiario_nombre' => fake()->name(),
            'beneficiario_documento' => fake()->numerify('########'),
            'beneficiario_profesion' => fake()->randomElement([
                'Ingeniero Civil',
                'Arquitecto',
                'Ingeniero Sanitario',
                'Técnico Constructor',
            ]),
            'cargo' => fake()->randomElement([
                'Residente de obra',
                'Asistente técnico',
                'Inspector',
                'Supervisor',
            ]),
            'fecha_inicio' => $inicio,
            'fecha_fin' => $fin,
            'descripcion' => fake()->paragraph(),
            'lugar_emision' => 'Puno, Perú',
            'emisor_nombre' => 'Ing. Roger Neptali Flores Coaquira',
            'emisor_cargo' => 'Consultor de Obras',
            'fecha_emision' => fake()->dateTimeBetween('-3 months', 'now'),
            'hash_verificacion' => hash('sha256', fake()->uuid()),
        ];
    }
}
