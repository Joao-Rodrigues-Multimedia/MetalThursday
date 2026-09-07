<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela das músicas.
 *
 * Uma música pode existir independentemente de qualquer lançamento e o título
 * não constitui a sua identidade, podendo repetir-se entre registos distintos.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Cria a tabela das músicas.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::create(
            'musicas',
            static function (Blueprint $tabela): void {
                $tabela->id();

                $tabela->string(
                    'titulo',
                    255,
                );

                $tabela
                    ->foreignId(
                        'criado_por_id',
                    )
                    ->nullable()
                    ->constrained(
                        table: 'utilizadores',
                    )
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $tabela
                    ->foreignId(
                        'atualizado_por_id',
                    )
                    ->nullable()
                    ->constrained(
                        table: 'utilizadores',
                    )
                    ->cascadeOnUpdate()
                    ->nullOnDelete();

                $tabela->timestamps();
                $tabela->softDeletes();

                $tabela->index(
                    [
                        'titulo',
                        'deleted_at',
                    ],
                    'musicas_titulo_estado_indice',
                );
            },
        );
    }

    /**
     * Elimina a tabela das músicas.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'musicas',
        );
    }
};
