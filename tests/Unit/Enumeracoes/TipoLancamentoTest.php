<?php

declare(strict_types=1);

namespace Tests\Unit\Enumeracoes;

use App\Enumeracoes\TipoLancamento;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Testa o contrato público dos tipos de lançamento musical.
 *
 * @since 2.0.0
 */
final class TipoLancamentoTest extends TestCase
{
    /**
     * Confirma os valores persistidos permitidos.
     *
     * @since 2.0.0
     */
    #[Test]
    public function define_tipos_de_lancamento_validos(): void
    {
        self::assertSame(
            [
                'album_estudio',
                'ep',
                'single',
                'album_ao_vivo',
                'compilacao',
                'demo',
                'split',
                'banda_sonora',
                'outro',
            ],
            array_map(
                static fn (
                    TipoLancamento $tipo,
                ): string => $tipo->value,
                TipoLancamento::cases(),
            ),
        );
    }

    /**
     * Confirma as etiquetas portuguesas apresentadas ao utilizador.
     *
     * @since 2.0.0
     */
    #[Test]
    public function devolve_etiquetas_portuguesas(): void
    {
        self::assertSame(
            'Álbum de estúdio',
            TipoLancamento::AlbumEstudio->etiqueta(),
        );

        self::assertSame(
            'EP',
            TipoLancamento::EP->etiqueta(),
        );

        self::assertSame(
            'Single',
            TipoLancamento::Single->etiqueta(),
        );

        self::assertSame(
            'Álbum ao vivo',
            TipoLancamento::AlbumAoVivo->etiqueta(),
        );

        self::assertSame(
            'Compilação',
            TipoLancamento::Compilacao->etiqueta(),
        );

        self::assertSame(
            'Demo',
            TipoLancamento::Demo->etiqueta(),
        );

        self::assertSame(
            'Split',
            TipoLancamento::Split->etiqueta(),
        );

        self::assertSame(
            'Banda sonora',
            TipoLancamento::BandaSonora->etiqueta(),
        );

        self::assertSame(
            'Outro',
            TipoLancamento::Outro->etiqueta(),
        );
    }
}
