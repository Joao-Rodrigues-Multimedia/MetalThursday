<?php

declare(strict_types=1);

namespace App\Servicos\Musica;

use App\Models\Musica\Artista;
use App\Models\Musica\FaixaLancamento;
use App\Models\Musica\Lancamento;
use App\Models\Musica\Musica;
use Illuminate\Support\Facades\DB;

/**
 * Importa lançamentos musicais obtidos através do Discogs.
 *
 * A integração Discogs é responsável pela consulta e normalização dos dados.
 * Este serviço decide quais desses dados pertencem ao catálogo persistido.
 *
 * @since 2.0.0
 */
final class ServicoImportacaoLancamento
{
    /**
     * Cria o serviço.
     *
     * @param  ServicoDiscogs  $discogs  Integração com o Discogs.
     *
     * @since 2.0.0
     */
    public function __construct(
        private readonly ServicoDiscogs $discogs,
    ) {}

    /**
     * Importa uma edição concreta do Discogs para o catálogo local.
     *
     * Uma edição já importada é reutilizada sem nova consulta externa.
     * Na primeira importação são persistidos o lançamento, os artistas,
     * as músicas e as respetivas ocorrências na tracklist.
     *
     * As faixas com artistas próprios utilizam esses artistas. Quando a
     * faixa não indica artistas próprios, são utilizados os artistas
     * principais da Release.
     *
     * @param  int  $identificadorDiscogs  Identificador da Release Discogs.
     * @return Lancamento Lançamento persistido.
     *
     * @since 2.0.0
     */
    public function importar(
        int $identificadorDiscogs,
    ): Lancamento {
        $existente =
            Lancamento::withTrashed()
                ->where(
                    'discogs_release_id',
                    $identificadorDiscogs,
                )
                ->first();

        if ($existente instanceof Lancamento) {
            if ($existente->trashed()) {
                $existente->restore();
            }

            return $existente;
        }

        $dados =
            $this
                ->discogs
                ->obterLancamento(
                    $identificadorDiscogs,
                );

        return DB::transaction(
            static function () use (
                $dados,
            ): Lancamento {
                $lancamento = new Lancamento;

                $lancamento->fill([
                    'titulo' => $dados['titulo'],
                    'tipo' => null,
                    'discogs_release_id' => $dados['discogs_release_id'],
                ]);

                $lancamento->saveOrFail();

                $identificadoresArtistas = [];

                foreach ($dados['artistas'] as $dadosArtista) {
                    $artista =
                        Artista::withTrashed()
                            ->where(
                                'discogs_id',
                                $dadosArtista['discogs_id'],
                            )
                            ->first();

                    if ($artista instanceof Artista) {
                        if ($artista->trashed()) {
                            $artista->restore();
                        }
                    } else {
                        $artista = new Artista;

                        $artista->fill([
                            'nome' => $dadosArtista['nome'],
                            'discogs_id' => $dadosArtista['discogs_id'],
                        ]);

                        $artista->saveOrFail();
                    }

                    $identificadoresArtistas[] =
                        $artista->getKey();
                }

                if ($identificadoresArtistas !== []) {
                    $lancamento
                        ->artistas()
                        ->syncWithoutDetaching(
                            $identificadoresArtistas,
                        );
                }

                foreach ($dados['faixas'] as $dadosFaixa) {
                    $musica = new Musica;

                    $musica->fill([
                        'titulo' => $dadosFaixa['titulo'],
                    ]);

                    $musica->saveOrFail();

                    $identificadoresArtistasFaixa =
                        $dadosFaixa['artistas'] === []
                        ? $identificadoresArtistas
                        : [];

                    if ($dadosFaixa['artistas'] !== []) {
                        foreach ($dadosFaixa['artistas'] as $dadosArtista) {
                            $artista =
                                Artista::withTrashed()
                                    ->where(
                                        'discogs_id',
                                        $dadosArtista['discogs_id'],
                                    )
                                    ->first();

                            if ($artista instanceof Artista) {
                                if ($artista->trashed()) {
                                    $artista->restore();
                                }
                            } else {
                                $artista = new Artista;

                                $artista->fill([
                                    'nome' => $dadosArtista['nome'],
                                    'discogs_id' => $dadosArtista['discogs_id'],
                                ]);

                                $artista->saveOrFail();
                            }

                            $identificadoresArtistasFaixa[] =
                                $artista->getKey();
                        }
                    }

                    if ($identificadoresArtistasFaixa !== []) {
                        $musica
                            ->artistas()
                            ->syncWithoutDetaching(
                                $identificadoresArtistasFaixa,
                            );
                    }

                    $faixa = new FaixaLancamento;

                    $faixa->fill([
                        'lancamento_id' => $lancamento->getKey(),
                        'musica_id' => $musica->getKey(),
                        'posicao' => $dadosFaixa['posicao'],
                        'ordem' => $dadosFaixa['ordem'],
                    ]);

                    $faixa->saveOrFail();
                }

                return $lancamento;
            },
        );
    }
}
