<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cria a tabela das faixas dos lançamentos.
 *
 * Cada registo representa uma ocorrência de uma música num lançamento e possui
 * identidade própria. O mesmo par lançamento/música pode repetir-se.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Cria a tabela das faixas dos lançamentos.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::create(
            'faixas_lancamento',
            static function (Blueprint $tabela): void {
                $tabela->id();

                $tabela
                    ->foreignId(
                        'lancamento_id',
                    )
                    ->constrained(
                        table: 'lancamentos',
                    )
                    ->cascadeOnDelete();

                $tabela
                    ->foreignId(
                        'musica_id',
                    )
                    ->constrained(
                        table: 'musicas',
                    )
                    ->cascadeOnDelete();

                $tabela->timestamps();
            },
        );
    }

    /**
     * Elimina a tabela das faixas dos lançamentos.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::dropIfExists(
            'faixas_lancamento',
        );
    }
};
