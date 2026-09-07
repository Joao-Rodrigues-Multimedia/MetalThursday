<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acrescenta a identificação da edição concreta do Discogs aos lançamentos.
 *
 * O identificador é opcional para permitir lançamentos sem correspondência
 * conhecida no Discogs. Quando existe, identifica univocamente uma Release
 * concreta.
 *
 * @since 2.0.0
 */
return new class extends Migration
{
    /**
     * Acrescenta o identificador da Release do Discogs.
     *
     * @since 2.0.0
     */
    public function up(): void
    {
        Schema::table(
            'lancamentos',
            static function (Blueprint $tabela): void {
                $tabela
                    ->unsignedBigInteger(
                        'discogs_release_id',
                    )
                    ->nullable()
                    ->unique(
                        'lancamentos_discogs_release_id_unico',
                    );
            },
        );
    }

    /**
     * Remove o identificador da Release do Discogs.
     *
     * @since 2.0.0
     */
    public function down(): void
    {
        Schema::table(
            'lancamentos',
            static function (Blueprint $tabela): void {
                $tabela->dropUnique(
                    'lancamentos_discogs_release_id_unico',
                );

                $tabela->dropColumn(
                    'discogs_release_id',
                );
            },
        );
    }
};
