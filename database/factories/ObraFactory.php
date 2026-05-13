<?php

namespace Database\Factories;

use App\Enums\EstadoObra;
use App\Models\Obra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Obra>
 */
class ObraFactory extends Factory
{
    protected $model = Obra::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fechaInicio = fake()->dateTimeBetween('-1 year', '+1 month');

        return [
            'codigo' => 'OBR-'.fake()->unique()->numerify('######'),
            'nombre' => 'Obra '.fake()->streetName(),
            'descripcion' => fake()->paragraph(),
            'ubicacion' => fake()->city().', '.fake()->state(),
            'entidad_contratante' => fake()->company(),
            'monto_contractual' => fake()->randomFloat(2, 50_000, 5_000_000),
            'fecha_inicio' => $fechaInicio,
            'fecha_fin_prevista' => fake()->dateTimeBetween($fechaInicio, '+2 years'),
            'fecha_fin_real' => null,
            'estado' => fake()->randomElement(EstadoObra::cases()),
            'creado_por' => null,
        ];
    }
}
