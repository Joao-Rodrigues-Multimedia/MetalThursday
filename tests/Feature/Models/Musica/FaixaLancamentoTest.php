<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Musica;

use App\Models\Musica\FaixaLancamento;
use App\Models\Musica\Lancamento;
use App\Models\Musica\Musica;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Testa os contratos persistidos das faixas dos lançamentos.
 *
 * @since 2.0.0
 */
final class FaixaLancamentoTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Confirma que uma faixa associa uma música a um lançamento.
     *
     * A associação possui identidade própria para poder receber atributos
     * específicos da ocorrência da música no lançamento no futuro.
     *
     * @since 2.0.0
     */
    #[Test]
    public function associa_musica_a_lancamento_com_identidade_propria(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $faixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        self::assertNotNull(
            $faixa->getKey(),
        );

        self::assertDatabaseHas(
            'faixas_lancamento',
            [
                'id' => $faixa->getKey(),
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ],
        );
    }

    /**
     * Confirma que uma música pode ocorrer várias vezes no mesmo lançamento.
     *
     * As ocorrências representam faixas distintas e, por isso, não existe uma
     * restrição de unicidade sobre o par lançamento/música.
     *
     * @since 2.0.0
     */
    #[Test]
    public function permite_repetir_musica_no_mesmo_lancamento(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $primeiraFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        $segundaFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        self::assertNotSame(
            $primeiraFaixa->getKey(),
            $segundaFaixa->getKey(),
        );

        self::assertSame(
            2,
            FaixaLancamento::query()
                ->where(
                    'lancamento_id',
                    $lancamento->getKey(),
                )
                ->where(
                    'musica_id',
                    $musica->getKey(),
                )
                ->count(),
        );
    }

    /**
     * Confirma que uma faixa conhece o lançamento a que pertence.
     *
     * @since 2.0.0
     */
    #[Test]
    public function pertence_a_lancamento(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $faixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        self::assertTrue(
            $faixa
                ->lancamento()
                ->firstOrFail()
                ->is(
                    $lancamento,
                ),
        );
    }

    /**
     * Confirma que uma faixa conhece a música que representa.
     *
     * @since 2.0.0
     */
    #[Test]
    public function pertence_a_musica(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $faixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        self::assertTrue(
            $faixa
                ->musica()
                ->firstOrFail()
                ->is(
                    $musica,
                ),
        );
    }

    /**
     * Confirma que um lançamento disponibiliza as suas faixas.
     *
     * @since 2.0.0
     */
    #[Test]
    public function lancamento_disponibiliza_as_suas_faixas(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $primeiraMusica = Musica::factory()
            ->create();

        $segundaMusica = Musica::factory()
            ->create();

        $primeiraFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $primeiraMusica->getKey(),
            ]);

        $segundaFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $segundaMusica->getKey(),
            ]);

        $faixas = $lancamento
            ->faixas()
            ->get();

        self::assertCount(
            2,
            $faixas,
        );

        self::assertTrue(
            $faixas->contains(
                'id',
                $primeiraFaixa->getKey(),
            ),
        );

        self::assertTrue(
            $faixas->contains(
                'id',
                $segundaFaixa->getKey(),
            ),
        );
    }

    /**
     * Confirma que uma música disponibiliza as ocorrências nos lançamentos.
     *
     * A mesma música pode ocorrer várias vezes, inclusive no mesmo lançamento.
     *
     * @since 2.0.0
     */
    #[Test]
    public function musica_disponibiliza_as_suas_faixas(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $primeiraFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        $segundaFaixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        $faixas = $musica
            ->faixas()
            ->get();

        self::assertCount(
            2,
            $faixas,
        );

        self::assertTrue(
            $faixas->contains(
                'id',
                $primeiraFaixa->getKey(),
            ),
        );

        self::assertTrue(
            $faixas->contains(
                'id',
                $segundaFaixa->getKey(),
            ),
        );
    }

    /**
     * Confirma que a faixa preserva o acesso a um lançamento eliminado logicamente.
     *
     * @since 2.0.0
     */
    #[Test]
    public function preserva_acesso_a_lancamento_eliminado_logicamente(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $faixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        $lancamento->delete();

        self::assertTrue(
            $faixa
                ->lancamento()
                ->firstOrFail()
                ->is(
                    $lancamento,
                ),
        );
    }

    /**
     * Confirma que a faixa preserva o acesso a uma música eliminada logicamente.
     *
     * @since 2.0.0
     */
    #[Test]
    public function preserva_acesso_a_musica_eliminada_logicamente(): void
    {
        $lancamento = Lancamento::factory()
            ->create();

        $musica = Musica::factory()
            ->create();

        $faixa = FaixaLancamento::factory()
            ->create([
                'lancamento_id' => $lancamento->getKey(),
                'musica_id' => $musica->getKey(),
            ]);

        $musica->delete();

        self::assertTrue(
            $faixa
                ->musica()
                ->firstOrFail()
                ->is(
                    $musica,
                ),
        );
    }
}
