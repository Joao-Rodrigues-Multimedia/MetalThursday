<?php

declare(strict_types=1);

namespace Database\Factories\Musica;

use App\Models\Musica\FaixaLancamento;
use App\Models\Musica\Lancamento;
use App\Models\Musica\Musica;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Cria dados de teste para faixas de lançamentos.
 *
 * O nome `Factory` permanece em inglês por corresponder à convenção de
 * descoberta automática das factories do Laravel.
 *
 * @extends Factory<FaixaLancamento>
 *
 * @since 2.0.0
 */
final class FaixaLancamentoFactory extends Factory
{
    /**
     * Modelo associado à factory.
     *
     * @var class-string<FaixaLancamento>
     *
     * @since 2.0.0
     */
    protected $model = FaixaLancamento::class;

    /**
     * Define os atributos predefinidos de uma faixa.
     *
     * @return array<string, mixed> Atributos da faixa.
     *
     * @since 2.0.0
     */
    public function definition(): array
    {
        return [
            'lancamento_id' => Lancamento::factory(),
            'musica_id' => Musica::factory(),
        ];
    }
}
