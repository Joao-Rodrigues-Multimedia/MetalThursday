<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela intermédia entre artistas e músicas.
 *
 * Cada associação entre um artista e uma música pode existir apenas uma vez.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Cria a tabela intermédia entre artistas e músicas.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::create(
            'artista_musica',
            static function (Blueprint $tabela): void {
                $tabela->foreignId(
                    'artista_id',
                );

                $tabela->foreignId(
                    'musica_id',
                );

                $tabela->primary(
                    [
                        'artista_id',
                        'musica_id',
                    ],
                    'artista_musica_primaria',
                );

                /*
                 * A chave primária começa por artista_id. Este índice
                 * adicional otimiza a obtenção dos artistas associados a uma
                 * música.
                 */
                $tabela->index(
                    'musica_id',
                    'artista_musica_musica_indice',
                );

                $tabela
                    ->foreign(
                        'artista_id',
                    )
                    ->references(
                        'id',
                    )
                    ->on(
                        'artistas',
                    )
                    ->cascadeOnDelete();

                $tabela
                    ->foreign(
                        'musica_id',
                    )
                    ->references(
                        'id',
                    )
                    ->on(
                        'musicas',
                    )
                    ->cascadeOnDelete();
            },
        );
    }

    /**
     * Elimina a tabela intermédia entre artistas e músicas.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'artista_musica',
        );
    }
};
