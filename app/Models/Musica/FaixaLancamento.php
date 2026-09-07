<?php

declare(strict_types=1);

namespace App\Models\Musica;

use Carbon\CarbonInterface;
use Database\Factories\Musica\FaixaLancamentoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Representa a ocorrência de uma música num lançamento.
 *
 * Cada faixa possui identidade própria. A mesma música pode ocorrer várias
 * vezes no mesmo lançamento.
 *
 * @property int $id
 * @property int $lancamento_id
 * @property int $musica_id
 * @property string|null $posicao
 * @property int|null $ordem
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property-read Lancamento $lancamento
 * @property-read Musica $musica
 *
 * @since 2.0.0
 */
class FaixaLancamento extends Model
{
    /** @use HasFactory<FaixaLancamentoFactory> */
    use HasFactory;

    /**
     * Nome físico da tabela associada ao modelo.
     *
     * @var string
     *
     * @since 2.0.0
     */
    protected $table = 'faixas_lancamento';

    /**
     * Atributos permitidos em operações de atribuição em massa.
     *
     * @var list<string>
     *
     * @since 2.0.0
     */
    protected $fillable = [
        'lancamento_id',
        'musica_id',
        'posicao',
        'ordem',
    ];

    /**
     * Define as conversões automáticas dos atributos persistidos.
     *
     * @return array<string, string> Conversões dos atributos.
     *
     * @since 2.0.0
     */
    protected function casts(): array
    {
        return [
            'lancamento_id' => 'integer',
            'musica_id' => 'integer',
            'ordem' => 'integer',
        ];
    }

    /**
     * Cria a factory associada ao modelo.
     *
     * @return FaixaLancamentoFactory Factory das faixas dos lançamentos.
     *
     * @since 2.0.0
     */
    protected static function newFactory(): FaixaLancamentoFactory
    {
        return FaixaLancamentoFactory::new();
    }

    /**
     * Obtém o lançamento a que a faixa pertence.
     *
     * @return BelongsTo<Lancamento, $this> Relação com o lançamento.
     *
     * @since 2.0.0
     */
    public function lancamento(): BelongsTo
    {
        return $this
            ->belongsTo(
                Lancamento::class,
                'lancamento_id',
            )
            ->withTrashed();
    }

    /**
     * Obtém a música representada pela faixa.
     *
     * @return BelongsTo<Musica, $this> Relação com a música.
     *
     * @since 2.0.0
     */
    public function musica(): BelongsTo
    {
        return $this
            ->belongsTo(
                Musica::class,
                'musica_id',
            )
            ->withTrashed();
    }
}
