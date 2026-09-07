<?php

declare(strict_types=1);

namespace App\Models\Musica;

use App\Enumeracoes\TipoLancamento;
use App\Traits\Auditoria\RegistaAutoria;
use Carbon\CarbonInterface;
use Database\Factories\Musica\LancamentoFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa um lançamento musical.
 *
 * O título não identifica univocamente o lançamento e pode, por isso, ser
 * repetido entre registos distintos.
 *
 * @property int $id
 * @property string $titulo
 * @property TipoLancamento|null $tipo
 * @property int|null $criado_por_id
 * @property int|null $atualizado_por_id
 * @property CarbonInterface|null $created_at
 * @property CarbonInterface|null $updated_at
 * @property CarbonInterface|null $deleted_at
 * @property-read Collection<int, Artista> $artistas
 * @property-read Collection<int, FaixaLancamento> $faixas
 *
 * @since 2.0.0
 */
class Lancamento extends Model
{
    /** @use HasFactory<LancamentoFactory> */
    use HasFactory;

    use RegistaAutoria;
    use SoftDeletes;

    /**
     * Comprimento máximo do título.
     *
     * @since 2.0.0
     */
    public const COMPRIMENTO_MAXIMO_TITULO = 255;

    /**
     * Nome da tabela intermédia entre artistas e lançamentos.
     *
     * @since 2.0.0
     */
    private const TABELA_ARTISTA_LANCAMENTO =
        'artista_lancamento';

    /**
     * Nome físico da tabela associada ao modelo.
     *
     * @var string
     *
     * @since 2.0.0
     */
    protected $table = 'lancamentos';

    /**
     * Atributos permitidos em operações de atribuição em massa.
     *
     * @var list<string>
     *
     * @since 2.0.0
     */
    protected $fillable = [
        'titulo',
        'tipo',
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
            'tipo' => TipoLancamento::class,
            'criado_por_id' => 'integer',
            'atualizado_por_id' => 'integer',
        ];
    }

    /**
     * Cria a factory associada ao modelo.
     *
     * @return LancamentoFactory Factory dos lançamentos.
     *
     * @since 2.0.0
     */
    protected static function newFactory(): LancamentoFactory
    {
        return LancamentoFactory::new();
    }

    /**
     * Obtém os artistas associados ao lançamento.
     *
     * @return BelongsToMany<Artista, $this> Relação com os artistas.
     *
     * @since 2.0.0
     */
    public function artistas(): BelongsToMany
    {
        return $this->belongsToMany(
            Artista::class,
            self::TABELA_ARTISTA_LANCAMENTO,
            'lancamento_id',
            'artista_id',
        );
    }

    /**
     * Obtém as faixas pertencentes ao lançamento.
     *
     * @return HasMany<FaixaLancamento, $this> Relação com as faixas.
     *
     * @since 2.0.0
     */
    public function faixas(): HasMany
    {
        return $this->hasMany(
            FaixaLancamento::class,
            'lancamento_id',
        );
    }
}
