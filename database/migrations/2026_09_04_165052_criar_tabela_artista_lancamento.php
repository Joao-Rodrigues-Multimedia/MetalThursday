<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela intermédia entre artistas e lançamentos.
 *
 * Cada associação entre um artista e um lançamento pode existir apenas uma vez.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Cria a tabela intermédia entre artistas e lançamentos.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::create(
            'artista_lancamento',
            static function (Blueprint $tabela): void {
                $tabela->foreignId(
                    'artista_id',
                );

                $tabela->foreignId(
                    'lancamento_id',
                );

                $tabela->primary(
                    [
                        'artista_id',
                        'lancamento_id',
                    ],
                    'artista_lancamento_primaria',
                );

                /*
                 * A chave primária começa por artista_id. Este índice
                 * adicional otimiza a obtenção dos artistas associados a um
                 * lançamento.
                 */
                $tabela->index(
                    'lancamento_id',
                    'artista_lancamento_lancamento_indice',
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
                        'lancamento_id',
                    )
                    ->references(
                        'id',
                    )
                    ->on(
                        'lancamentos',
                    )
                    ->cascadeOnDelete();
            },
        );
    }

    /**
     * Elimina a tabela intermédia entre artistas e lançamentos.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'artista_lancamento',
        );
    }
};
