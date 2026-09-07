<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Musica;

use App\Models\Musica\Artista;
use App\Models\Musica\Musica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testa os contratos persistidos do modelo das músicas.
 *
 * @since 2.0.0
 */
final class MusicaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirma que uma música pode existir sem estar associada a um lançamento.
     *
     * A música constitui uma entidade independente do catálogo e não necessita
     * de pertencer a um lançamento para poder ser persistida.
     *
     * @since 2.0.0
     */
    #[Test]
    public function permite_musica_sem_lancamento(): void
    {
        $musica = Musica::factory()
            ->create([
                'titulo' => 'Orion',
            ]);

        self::assertDatabaseHas(
            'musicas',
            [
                'id' => $musica->getKey(),
                'titulo' => 'Orion',
            ],
        );
    }

    /**
     * Confirma que músicas distintas podem possuir o mesmo título.
     *
     * O título não identifica univocamente uma música.
     *
     * @since 2.0.0
     */
    #[Test]
    public function permite_musicas_com_o_mesmo_titulo(): void
    {
        $primeiraMusica = Musica::factory()
            ->create([
                'titulo' => 'One',
            ]);

        $segundaMusica = Musica::factory()
            ->create([
                'titulo' => 'One',
            ]);

        self::assertNotSame(
            $primeiraMusica->getKey(),
            $segundaMusica->getKey(),
        );

        self::assertSame(
            2,
            Musica::query()
                ->where(
                    'titulo',
                    'One',
                )
                ->count(),
        );
    }

    /**
     * Confirma que a remoção de uma música preserva o registo histórico.
     *
     * @since 2.0.0
     */
    #[Test]
    public function elimina_musica_logicamente(): void
    {
        $musica = Musica::factory()
            ->create([
                'titulo' => 'Música removida',
            ]);

        $musica->delete();

        self::assertSoftDeleted(
            'musicas',
            [
                'id' => $musica->getKey(),
            ],
        );
    }

    /**
     * Confirma que um artista pode estar associado a várias músicas.
     *
     * @since 2.0.0
     */
    #[Test]
    public function permite_artista_com_varias_musicas(): void
    {
        $artista = Artista::factory()
            ->create();

        $primeiraMusica = Musica::factory()
            ->create([
                'titulo' => 'Primeira música',
            ]);

        $segundaMusica = Musica::factory()
            ->create([
                'titulo' => 'Segunda música',
            ]);

        $artista
            ->musicas()
            ->attach([
                $primeiraMusica->getKey(),
                $segundaMusica->getKey(),
            ]);

        $musicas = $artista
            ->musicas()
            ->get();

        self::assertCount(
            2,
            $musicas,
        );

        self::assertTrue(
            $musicas->contains(
                'id',
                $primeiraMusica->getKey(),
            ),
        );

        self::assertTrue(
            $musicas->contains(
                'id',
                $segundaMusica->getKey(),
            ),
        );
    }

    /**
     * Confirma que uma música pode estar associada a vários artistas.
     *
     * @since 2.0.0
     */
    #[Test]
    public function permite_musica_com_varios_artistas(): void
    {
        $musica = Musica::factory()
            ->create([
                'titulo' => 'Música colaborativa',
            ]);

        $primeiroArtista = Artista::factory()
            ->create();

        $segundoArtista = Artista::factory()
            ->create();

        $musica
            ->artistas()
            ->attach([
                $primeiroArtista->getKey(),
                $segundoArtista->getKey(),
            ]);

        $artistas = $musica
            ->artistas()
            ->get();

        self::assertCount(
            2,
            $artistas,
        );

        self::assertTrue(
            $artistas->contains(
                'id',
                $primeiroArtista->getKey(),
            ),
        );

        self::assertTrue(
            $artistas->contains(
                'id',
                $segundoArtista->getKey(),
            ),
        );
    }
}
