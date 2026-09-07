<?php

declare(strict_types=1);

namespace App\Enumeracoes;

/**
 * Representa os tipos conhecidos de lançamento musical.
 *
 * A ausência de valor representa um tipo desconhecido ou não indicado.
 *
 * @since 2.0.0
 */
enum TipoLancamento: string
{
    /**
     * Álbum de estúdio.
     *
     * @since 2.0.0
     */
    case AlbumEstudio = 'album_estudio';

    /**
     * Extended play.
     *
     * @since 2.0.0
     */
    case EP = 'ep';

    /**
     * Single.
     *
     * @since 2.0.0
     */
    case Single = 'single';

    /**
     * Álbum gravado ao vivo.
     *
     * @since 2.0.0
     */
    case AlbumAoVivo = 'album_ao_vivo';

    /**
     * Compilação.
     *
     * @since 2.0.0
     */
    case Compilacao = 'compilacao';

    /**
     * Demo.
     *
     * @since 2.0.0
     */
    case Demo = 'demo';

    /**
     * Lançamento partilhado por vários artistas.
     *
     * @since 2.0.0
     */
    case Split = 'split';

    /**
     * Banda sonora.
     *
     * @since 2.0.0
     */
    case BandaSonora = 'banda_sonora';

    /**
     * Outro tipo conhecido não abrangido pela taxonomia.
     *
     * @since 2.0.0
     */
    case Outro = 'outro';

    /**
     * Obtém a etiqueta apresentada ao utilizador.
     *
     * @return string Etiqueta legível.
     *
     * @since 2.0.0
     */
    public function etiqueta(): string
    {
        return match ($this) {
            self::AlbumEstudio => 'Álbum de estúdio',
            self::EP => 'EP',
            self::Single => 'Single',
            self::AlbumAoVivo => 'Álbum ao vivo',
            self::Compilacao => 'Compilação',
            self::Demo => 'Demo',
            self::Split => 'Split',
            self::BandaSonora => 'Banda sonora',
            self::Outro => 'Outro',
        };
    }
}
