<?php

declare(strict_types=1);

namespace Database\Factories\Musica;

use App\Models\Musica\Lancamento;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Cria dados de teste para lançamentos musicais.
 *
 * O nome `Factory` permanece em inglês por corresponder à convenção de
 * descoberta automática das factories do Laravel.
 *
 * @extends Factory<Lancamento>
 *
 * @since 2.0.0
 */
final class LancamentoFactory extends Factory
{
    /**
     * Modelo associado à factory.
     *
     * @var class-string<Lancamento>
     *
     * @since 2.0.0
     */
    protected $model = Lancamento::class;

    /**
     * Define os atributos predefinidos de um lançamento.
     *
     * O tipo permanece nulo por predefinição para não inventar informação
     * desconhecida.
     *
     * @return array<string, mixed> Atributos do lançamento.
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

            'tipo' => null,
        ];
    }
}
