<?php

declare(strict_types=1);

namespace App\Models\Musica;

use App\Traits\Auditoria\RegistaAutoria;
use Carbon\CarbonInterface;
use Database\Factories\Musica\MusicaFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Representa uma música do catálogo.
 *
 * A música constitui uma entidade independente e pode existir sem estar
 * associada a qualquer lançamento. O título não a identifica univocamente.
 *
 * @property int $id
 * @property string $titulo
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
class Musica extends Model
{
    /** @use HasFactory<MusicaFactory> */
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
     * Nome da tabela intermédia entre artistas e músicas.
     *
     * @since 2.0.0
     */
    private const TABELA_ARTISTA_MUSICA =
        'artista_musica';

    /**
     * Nome físico da tabela associada ao modelo.
     *
     * @var string
     *
     * @since 2.0.0
     */
    protected $table = 'musicas';

    /**
     * Atributos permitidos em operações de atribuição em massa.
     *
     * @var list<string>
     *
     * @since 2.0.0
     */
    protected $fillable = [
        'titulo',
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
            'criado_por_id' => 'integer',
            'atualizado_por_id' => 'integer',
        ];
    }

    /**
     * Cria a factory associada ao modelo.
     *
     * @return MusicaFactory Factory das músicas.
     *
     * @since 2.0.0
     */
    protected static function newFactory(): MusicaFactory
    {
        return MusicaFactory::new();
    }

    /**
     * Obtém os artistas associados à música.
     *
     * @return BelongsToMany<Artista, $this> Relação com os artistas.
     *
     * @since 2.0.0
     */
    public function artistas(): BelongsToMany
    {
        return $this->belongsToMany(
            Artista::class,
            self::TABELA_ARTISTA_MUSICA,
            'musica_id',
            'artista_id',
        );
    }

    /**
     * Obtém as ocorrências da música nos lançamentos.
     *
     * @return HasMany<FaixaLancamento, $this> Relação com as faixas.
     *
     * @since 2.0.0
     */
    public function faixas(): HasMany
    {
        return $this->hasMany(
            FaixaLancamento::class,
            'musica_id',
        );
    }
}
