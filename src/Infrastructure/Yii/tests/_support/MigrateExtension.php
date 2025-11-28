<?php

use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Extension;
use Codeception\Events;

/**
 * MigrateExtension
 *
 * - Antes do suite funcional: executa todas as migrations (migrate up).
 * - Após cada teste funcional: TRUNCATE nas tabelas utilizadas (mantém o schema).
 * - Depois do suite funcional: migrate/down --all.
 */
class MigrateExtension extends Extension
{
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_AFTER   => 'afterTest',
        Events::SUITE_AFTER  => 'afterSuite',
    ];

    /** @var string[] Tabelas que os testes funcionais manipulam; ordem importa por FKs */
    private array $tablesToTruncate = [
        'product',
        'product_type',
    ];

    /** @var bool Flag para indicar que estamos dentro do suite funcional */
    private bool $functionalSuiteRunning = false;

    /**
     * Executa migrations antes do suite funcional.
     */
    public function beforeSuite(SuiteEvent $event): void
    {
        if (!$this->isFunctionalSuite($event)) {
            return;
        }

        $this->functionalSuiteRunning = true;

        $this->runConsole(function (): void {
            // Aguarda DB disponível antes de tentar rodar migrations (ajusta timeout conforme necessário)
            $this->waitForDatabaseReady(30, 1);

            // Executa migrate/up explicitamente (não interativo)
            $exit = \Yii::$app->runAction('migrate/up', ['interactive' => false]);
            if ($exit !== 0) {
                throw new \RuntimeException('Falha ao executar migrations (migrate up) para testes funcionais. Exit code: ' . (string)$exit);
            }
        });
    }

    /**
     * Após cada teste funcional, limpa dados das tabelas usadas.
     */
    public function afterTest(TestEvent $event): void
    {
        if (!$this->functionalSuiteRunning) {
            return;
        }

        // Usa a app web atual criada pelo módulo Yii2 do Codeception
        $db = \Yii::$app->db ?? null;
        if ($db === null) {
            return;
        }

        $db->createCommand('SET FOREIGN_KEY_CHECKS=0')->execute();
        foreach ($this->tablesToTruncate as $table) {
            $db->createCommand("TRUNCATE TABLE {{%$table}}")->execute();
        }
        $db->createCommand('SET FOREIGN_KEY_CHECKS=1')->execute();
    }

    /**
     * Ao fim do suite funcional, dá rollback de todas as migrations (opcional).
     */
    public function afterSuite(SuiteEvent $event): void
    {
        if (!$this->isFunctionalSuite($event)) {
            return;
        }

        $this->functionalSuiteRunning = false;
    }

    /**
     * Executa um bloco dentro de uma aplicação console Yii2, usando o DB de testes.
     * Restaura o Yii::$app anterior ao terminar.
     *
     * Agora suprime qualquer saída gerada pelo console (migrate messages, echoes, etc.)
     * para evitar que conteúdo seja enviado antes do início dos testes web (evita "headers already sent").
     */
    private function runConsole(callable $callback): void
    {
        // Guarda a app atual (web) para restaurar depois
        $previousApp = \Yii::$app ?? null;

        // Caminhos base (esta extensão está em src/Infrastructure/Yii/tests/_support)
        $basePath = dirname(__DIR__, 2); // -> src/Infrastructure/Yii
        $vendor   = $basePath . '/vendor';

        // Autoloaders e Yii core
        $autoload = $vendor . '/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        $yiiCore = $vendor . '/yiisoft/yii2/Yii.php';
        if (file_exists($yiiCore)) {
            require_once $yiiCore;
        }

        // Config da console app + DB de teste
        $consoleConfig = require $basePath . '/config/console.php';
        $testDbConfig  = require $basePath . '/config/test_db.php';
        $consoleConfig['components']['db'] = $testDbConfig;

        // Garante ambiente de teste
        defined('YII_ENV') || define('YII_ENV', 'test');
        defined('YII_DEBUG') || define('YII_DEBUG', true);

        // Instancia app console e torna-a a Yii::$app enquanto o callback roda
        $consoleApp = new yii\console\Application($consoleConfig);
        \Yii::$app = $consoleApp;

        // Inicia buffer de saída para suprimir todo output gerado pela execução do console
        $obStarted = false;
        try {
            if (function_exists('ob_start')) {
                ob_start();
                $obStarted = true;
            }
            try {
                $callback();
            } catch (\yii\base\ExitException $e) {
                // Ignora saídas do console (comandos migrate podem chamar exit internamente)
            }
        } finally {
            // Limpa e fecha buffer de saída se foi iniciado
            if ($obStarted && function_exists('ob_get_level')) {
                while (ob_get_level() > 0) {
                    @ob_end_clean();
                }
            }

            try {
                // finaliza app console se suportado
                $consoleApp->end();
            } catch (\yii\base\ExitException $e) {
                // Ignora ExitException ao finalizar a app console
            }
            // Restaura app anterior (web) ou null
            if ($previousApp !== null) {
                \Yii::$app = $previousApp;
            } else {
                \Yii::$app = null;
            }
        }
    }

    /**
     * Espera até o banco de dados ficar disponível.
     *
     * @param int $maxAttempts número máximo de tentativas
     * @param int $sleepSeconds segundos entre tentativas
     * @throws \RuntimeException se o DB não ficar disponível em time
     */
    private function waitForDatabaseReady(int $maxAttempts = 30, int $sleepSeconds = 1): void
    {
        $attempt = 0;
        while ($attempt < $maxAttempts) {
            $attempt++;
            try {
                // Acesso direto ao componente db da app atual (console app setada em runConsole)
                $db = \Yii::$app->has('db') ? \Yii::$app->get('db') : null;
                if ($db === null) {
                    throw new \RuntimeException('Component db não encontrado na aplicação console.');
                }

                // Tenta uma query simples
                $db->createCommand('SELECT 1')->queryScalar();
                return; // DB disponível
            } catch (\Throwable $e) {
                // Aguarda e tenta novamente
                if ($attempt >= $maxAttempts) {
                    throw new \RuntimeException(sprintf(
                        'Database did not become ready after %d attempts (%d seconds). Last error: %s',
                        $maxAttempts,
                        $maxAttempts * $sleepSeconds,
                        $e->getMessage()
                    ));
                }
                sleep($sleepSeconds);
            }
        }
    }

    private function isFunctionalSuite(SuiteEvent $event): bool
    {
        $suite = $event->getSuite();

        if (method_exists($suite, 'getBaseName')) {
            return $suite->getBaseName() === 'functional';
        }

        if (method_exists($suite, 'getName')) {
            return $suite->getName() === 'functional';
        }

        return false;
    }
}
