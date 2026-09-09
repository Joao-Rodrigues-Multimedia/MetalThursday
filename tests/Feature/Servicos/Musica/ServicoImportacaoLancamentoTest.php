<?php

declare(strict_types=1);

namespace Tests\Feature\Servicos\Musica;

use App\Models\Musica\Artista;
use App\Models\Musica\Lancamento;
use App\Models\Musica\Musica;
use App\Servicos\Musica\ServicoImportacaoLancamento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testa a importação persistente de lançamentos musicais.
 *
 * @since 2.0.0
 */
final class ServicoImportacaoLancamentoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Configura a integração Discogs utilizada pelos testes.
     *
     * @since 2.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        config()->set(
            'discogs.base_url',
            'https://api.discogs.com',
        );

        config()->set(
            'discogs.user_agent',
            'MetalThursdayTest/2.0',
        );

        config()->set(
            'discogs.tentativas',
            3,
        );

        config()->set(
            'discogs.intervalo_repeticao_ms',
            0,
        );

        config()->set(
            'discogs.intervalo_minimo_pedidos_ms',
            0,
        );
    }

    /**
     * Confirma a persistência de uma edição concreta obtida do Discogs.
     *
     * @since 2.0.0
     */
    #[Test]
    public function importa_lancamento_base_do_discogs(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',
                    'artists' => [],
                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        self::assertInstanceOf(
            Lancamento::class,
            $lancamento,
        );

        self::assertTrue(
            $lancamento->exists,
        );

        self::assertSame(
            'Master Of Puppets',
            $lancamento->titulo,
        );

        self::assertSame(
            249504,
            $lancamento->discogs_release_id,
        );

        self::assertNull(
            $lancamento->tipo,
        );

        $this->assertDatabaseHas(
            'lancamentos',
            [
                'id' => $lancamento->getKey(),
                'titulo' => 'Master Of Puppets',
                'discogs_release_id' => 249504,
                'tipo' => null,
            ],
        );
    }

    /**
     * Confirma que uma edição Discogs já importada é reutilizada.
     *
     * @since 2.0.0
     */
    #[Test]
    public function reutiliza_lancamento_discogs_ja_importado(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',
                    'artists' => [],
                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $servico =
            app(
                ServicoImportacaoLancamento::class,
            );

        $primeiroLancamento =
            $servico->importar(
                249504,
            );

        $segundoLancamento =
            $servico->importar(
                249504,
            );

        self::assertSame(
            $primeiroLancamento->getKey(),
            $segundoLancamento->getKey(),
        );

        self::assertSame(
            1,
            Lancamento::query()
                ->where(
                    'discogs_release_id',
                    249504,
                )
                ->count(),
        );

        Http::assertSentCount(
            1,
        );
    }

    /**
     * Confirma que uma edição Discogs eliminada logicamente é restaurada.
     *
     * @since 2.0.0
     */
    #[Test]
    public function restaura_lancamento_discogs_eliminado(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',
                    'artists' => [],
                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $servico =
            app(
                ServicoImportacaoLancamento::class,
            );

        $lancamento =
            $servico->importar(
                249504,
            );

        $identificador =
            $lancamento->getKey();

        $lancamento->delete();

        self::assertSoftDeleted(
            'lancamentos',
            [
                'id' => $identificador,
            ],
        );

        $restaurado =
            $servico->importar(
                249504,
            );

        self::assertSame(
            $identificador,
            $restaurado->getKey(),
        );

        self::assertFalse(
            $restaurado->trashed(),
        );

        self::assertSame(
            1,
            Lancamento::withTrashed()
                ->where(
                    'discogs_release_id',
                    249504,
                )
                ->count(),
        );

        Http::assertSentCount(
            1,
        );
    }

    /**
     * Confirma que um artista Discogs já existente é associado ao lançamento.
     *
     * @since 2.0.0
     */
    #[Test]
    public function associa_artista_discogs_ja_existente(): void
    {
        $artista =
            Artista::factory()
                ->create([
                    'nome' => 'Metallica',
                    'discogs_id' => 18839,
                ]);

        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => 'Metallica',
                        ],
                    ],

                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $artistas =
            $lancamento
                ->artistas()
                ->get();

        self::assertCount(
            1,
            $artistas,
        );

        self::assertSame(
            $artista->getKey(),
            $artistas->first()?->getKey(),
        );

        self::assertSame(
            1,
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->count(),
        );
    }

    /**
     * Confirma que um artista Discogs inexistente é criado e associado.
     *
     * @since 2.0.0
     */
    #[Test]
    public function cria_artista_discogs_inexistente(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => 'Metallica',
                        ],
                    ],

                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $artista =
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->first();

        self::assertInstanceOf(
            Artista::class,
            $artista,
        );

        self::assertSame(
            'Metallica',
            $artista->nome,
        );

        self::assertTrue(
            $lancamento
                ->artistas()
                ->whereKey(
                    $artista->getKey(),
                )
                ->exists(),
        );

        self::assertSame(
            1,
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->count(),
        );
    }

    /**
     * Confirma que um artista Discogs eliminado logicamente é restaurado.
     *
     * @since 2.0.0
     */
    #[Test]
    public function restaura_artista_discogs_eliminado(): void
    {
        $artista =
            Artista::factory()
                ->create([
                    'nome' => 'Metallica',
                    'discogs_id' => 18839,
                ]);

        $identificadorArtista =
            $artista->getKey();

        $artista->delete();

        self::assertSoftDeleted(
            'artistas',
            [
                'id' => $identificadorArtista,
            ],
        );

        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => 'Metallica',
                        ],
                    ],

                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $restaurado =
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->first();

        self::assertInstanceOf(
            Artista::class,
            $restaurado,
        );

        self::assertSame(
            $identificadorArtista,
            $restaurado->getKey(),
        );

        self::assertFalse(
            $restaurado->trashed(),
        );

        self::assertTrue(
            $lancamento
                ->artistas()
                ->whereKey(
                    $identificadorArtista,
                )
                ->exists(),
        );

        self::assertSame(
            1,
            Artista::withTrashed()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->count(),
        );
    }

    /**
     * Confirma que uma falha durante a persistência reverte toda a importação.
     *
     * @since 2.0.0
     */
    #[Test]
    public function reverte_importacao_perante_falha_de_persistencia(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => str_repeat(
                                'A',
                                256,
                            ),
                        ],
                    ],

                    'tracklist' => [],
                ],
                200,
            ),
        ]);

        try {
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

            self::fail(
                'Era esperada uma falha durante a persistência do artista.',
            );
        } catch (InvalidArgumentException) {
            // A falha é esperada para validar a atomicidade da operação.
        }

        $this->assertDatabaseMissing(
            'lancamentos',
            [
                'discogs_release_id' => 249504,
            ],
        );

        $this->assertDatabaseMissing(
            'artistas',
            [
                'discogs_id' => 18839,
            ],
        );
    }

    /**
     * Confirma a persistência das músicas e das respetivas ocorrências na Release.
     *
     * @since 2.0.0
     */
    #[Test]
    public function importa_musicas_e_faixas_do_lancamento(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',
                    'artists' => [],

                    'tracklist' => [
                        [
                            'position' => 'A1',
                            'type_' => 'track',
                            'title' => 'Battery',
                        ],
                        [
                            'position' => 'A2',
                            'type_' => 'track',
                            'title' => 'Master Of Puppets',
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $faixas =
            $lancamento
                ->faixas()
                ->with(
                    'musica',
                )
                ->get();

        self::assertCount(
            2,
            $faixas,
        );

        self::assertSame(
            'Battery',
            $faixas[0]->musica->titulo,
        );

        self::assertSame(
            'A1',
            $faixas[0]->posicao,
        );

        self::assertSame(
            1,
            $faixas[0]->ordem,
        );

        self::assertSame(
            'Master Of Puppets',
            $faixas[1]->musica->titulo,
        );

        self::assertSame(
            'A2',
            $faixas[1]->posicao,
        );

        self::assertSame(
            2,
            $faixas[1]->ordem,
        );

        self::assertSame(
            2,
            Musica::query()
                ->count(),
        );
    }

    /**
     * Confirma que um artista explícito da faixa já existente é associado à música.
     *
     * @since 2.0.0
     */
    #[Test]
    public function associa_artista_existente_indicado_na_faixa(): void
    {
        $artista =
            Artista::factory()
                ->create([
                    'nome' => 'Artista Convidado',
                    'discogs_id' => 987654,
                ]);

        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Lançamento de Exemplo',
                    'artists' => [],

                    'tracklist' => [
                        [
                            'position' => '1',
                            'type_' => 'track',
                            'title' => 'Música Convidada',

                            'artists' => [
                                [
                                    'id' => 987654,
                                    'name' => 'Artista Convidado',
                                ],
                            ],
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $musica =
            $lancamento
                ->faixas()
                ->firstOrFail()
                ->musica;

        $artistas =
            $musica
                ->artistas()
                ->get();

        self::assertCount(
            1,
            $artistas,
        );

        self::assertSame(
            $artista->getKey(),
            $artistas->first()?->getKey(),
        );

        self::assertSame(
            1,
            Artista::query()
                ->where(
                    'discogs_id',
                    987654,
                )
                ->count(),
        );
    }

    /**
     * Confirma que um artista inexistente indicado na faixa é criado e associado.
     *
     * @since 2.0.0
     */
    #[Test]
    public function cria_artista_inexistente_indicado_na_faixa(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Lançamento de Exemplo',
                    'artists' => [],

                    'tracklist' => [
                        [
                            'position' => '1',
                            'type_' => 'track',
                            'title' => 'Música Convidada',

                            'artists' => [
                                [
                                    'id' => 987654,
                                    'name' => 'Artista Convidado',
                                ],
                            ],
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $artista =
            Artista::query()
                ->where(
                    'discogs_id',
                    987654,
                )
                ->first();

        self::assertInstanceOf(
            Artista::class,
            $artista,
        );

        self::assertSame(
            'Artista Convidado',
            $artista->nome,
        );

        $musica =
            $lancamento
                ->faixas()
                ->firstOrFail()
                ->musica;

        self::assertTrue(
            $musica
                ->artistas()
                ->whereKey(
                    $artista->getKey(),
                )
                ->exists(),
        );

        self::assertSame(
            0,
            $lancamento
                ->artistas()
                ->count(),
        );
    }

    /**
     * Confirma que um artista eliminado indicado numa faixa é restaurado.
     *
     * @since 2.0.0
     */
    #[Test]
    public function restaura_artista_eliminado_indicado_na_faixa(): void
    {
        $artista =
            Artista::factory()
                ->create([
                    'nome' => 'Artista Convidado',
                    'discogs_id' => 987654,
                ]);

        $identificadorArtista =
            $artista->getKey();

        $artista->delete();

        self::assertSoftDeleted(
            'artistas',
            [
                'id' => $identificadorArtista,
            ],
        );

        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Lançamento de Exemplo',
                    'artists' => [],

                    'tracklist' => [
                        [
                            'position' => '1',
                            'type_' => 'track',
                            'title' => 'Música Convidada',

                            'artists' => [
                                [
                                    'id' => 987654,
                                    'name' => 'Artista Convidado',
                                ],
                            ],
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $restaurado =
            Artista::query()
                ->where(
                    'discogs_id',
                    987654,
                )
                ->first();

        self::assertInstanceOf(
            Artista::class,
            $restaurado,
        );

        self::assertSame(
            $identificadorArtista,
            $restaurado->getKey(),
        );

        self::assertFalse(
            $restaurado->trashed(),
        );

        $musica =
            $lancamento
                ->faixas()
                ->firstOrFail()
                ->musica;

        self::assertTrue(
            $musica
                ->artistas()
                ->whereKey(
                    $identificadorArtista,
                )
                ->exists(),
        );

        self::assertSame(
            0,
            $lancamento
                ->artistas()
                ->count(),
        );

        self::assertSame(
            1,
            Artista::withTrashed()
                ->where(
                    'discogs_id',
                    987654,
                )
                ->count(),
        );
    }

    /**
     * Confirma que uma faixa sem artistas próprios herda os artistas da Release.
     *
     * @since 2.0.0
     */
    #[Test]
    public function associa_artistas_do_lancamento_a_faixa_sem_artistas_proprios(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Master Of Puppets',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => 'Metallica',
                        ],
                    ],

                    'tracklist' => [
                        [
                            'position' => 'A1',
                            'type_' => 'track',
                            'title' => 'Battery',
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $artista =
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->firstOrFail();

        $musica =
            $lancamento
                ->faixas()
                ->firstOrFail()
                ->musica;

        $artistasMusica =
            $musica
                ->artistas()
                ->get();

        self::assertCount(
            1,
            $artistasMusica,
        );

        self::assertSame(
            $artista->getKey(),
            $artistasMusica->first()?->getKey(),
        );

        self::assertTrue(
            $lancamento
                ->artistas()
                ->whereKey(
                    $artista->getKey(),
                )
                ->exists(),
        );
    }

    /**
     * Confirma que artistas próprios da faixa substituem o fallback dos artistas da Release.
     *
     * @since 2.0.0
     */
    #[Test]
    public function artistas_proprios_da_faixa_tem_prioridade_sobre_artistas_do_lancamento(): void
    {
        Http::fake([
            'https://api.discogs.com/releases/249504' => Http::response(
                [
                    'id' => 249504,
                    'title' => 'Lançamento de Exemplo',

                    'artists' => [
                        [
                            'id' => 18839,
                            'name' => 'Artista Principal',
                        ],
                    ],

                    'tracklist' => [
                        [
                            'position' => '1',
                            'type_' => 'track',
                            'title' => 'Música Convidada',

                            'artists' => [
                                [
                                    'id' => 987654,
                                    'name' => 'Artista Convidado',
                                ],
                            ],
                        ],
                    ],
                ],
                200,
            ),
        ]);

        $lancamento =
            app(
                ServicoImportacaoLancamento::class,
            )->importar(
                249504,
            );

        $artistaPrincipal =
            Artista::query()
                ->where(
                    'discogs_id',
                    18839,
                )
                ->firstOrFail();

        $artistaConvidado =
            Artista::query()
                ->where(
                    'discogs_id',
                    987654,
                )
                ->firstOrFail();

        $musica =
            $lancamento
                ->faixas()
                ->firstOrFail()
                ->musica;

        $identificadoresArtistasMusica =
            $musica
                ->artistas()
                ->pluck(
                    'artistas.id',
                )
                ->all();

        self::assertSame(
            [
                $artistaConvidado->getKey(),
            ],
            $identificadoresArtistasMusica,
        );

        self::assertTrue(
            $lancamento
                ->artistas()
                ->whereKey(
                    $artistaPrincipal->getKey(),
                )
                ->exists(),
        );

        self::assertFalse(
            $lancamento
                ->artistas()
                ->whereKey(
                    $artistaConvidado->getKey(),
                )
                ->exists(),
        );
    }
}
