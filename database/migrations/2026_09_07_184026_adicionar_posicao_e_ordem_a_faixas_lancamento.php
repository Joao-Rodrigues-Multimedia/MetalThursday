<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acrescenta a posição e a ordem das faixas nos lançamentos.
 *
 * A posição preserva a representação textual da edição concreta, enquanto a
 * ordem conserva a sequência efetiva da faixa na tracklist.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Acrescenta os metadados de ordenação das faixas.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::table(
            'faixas_lancamento',
            static function (Blueprint $tabela): void {
                $tabela
                    ->string(
                        'posicao',
                        100,
                    )
                    ->nullable();

                $tabela
                    ->unsignedInteger(
                        'ordem',
                    )
                    ->nullable();
            },
        );
    }

    /**
     * Remove os metadados de ordenação das faixas.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::table(
            'faixas_lancamento',
            static function (Blueprint $tabela): void {
                $tabela->dropColumn([
                    'posicao',
                    'ordem',
                ]);
            },
        );
    }
};
