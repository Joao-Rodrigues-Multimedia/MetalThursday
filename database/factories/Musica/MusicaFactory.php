<?php

declare(strict_types=1);

namespace Database\Factories\Musica;

use App\Models\Musica\Musica;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Cria dados de teste para músicas.
 *
 * O nome `Factory` permanece em inglês por corresponder à convenção de
 * descoberta automática das factories do Laravel.
 *
 * @extends Factory<Musica>
 *
 * @since 2.0.0
 */
final class MusicaFactory extends Factory
{
    /**
     * Modelo associado à factory.
     *
     * @var class-string<Musica>
     *
     * @since 2.0.0
     */
    protected $model = Musica::class;

    /**
     * Define os atributos predefinidos de uma música.
     *
     * @return array<string, mixed> Atributos da música.
     *
     * @since 2.0.0
     */
    public function definition(): array
    {
        return [
            'titulo' => Str::ucfirst(
                $this
                    ->faker
                    ->words(
                        3,
                        true,
                    ),
            ),
        ];
    }
}
