<?php
declare(strict_types=1);

namespace App\Tests\Common;

use App\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\Service\ResetInterface;

class KernelLifecycleManager
{
    /**
     * @var KernelInterface|null
     */
    protected static $kernel;

    /**
     * Get the currently active kernel
     */
    public static function getKernel(): KernelInterface
    {
        if (static::$kernel) {
            static::$kernel->boot();

            return static::$kernel;
        }

        return static::bootKernel();
    }

    public static function bootKernel(): KernelInterface
    {
        self::ensureKernelShutdown();

        static::$kernel = static::createKernel();
        static::$kernel->boot();

        return static::$kernel;
    }

    public static function createKernel(): KernelInterface
    {
        if (isset($_ENV['APP_ENV'])) {
            $env = $_ENV['APP_ENV'];
        } elseif (isset($_SERVER['APP_ENV'])) {
            $env = $_SERVER['APP_ENV'];
        } else {
            $env = 'test';
        }

        if (isset($_ENV['APP_DEBUG'])) {
            $debug = (bool) $_ENV['APP_DEBUG'];
        } elseif (isset($_SERVER['APP_DEBUG'])) {
            $debug = (bool) $_SERVER['APP_DEBUG'];
        } else {
            $debug = true;
        }

        return new Kernel($env, $debug);
    }

    private static function ensureKernelShutdown(): void
    {
        if (static::$kernel === null) {
            return;
        }

        $container = static::$kernel->getContainer();
        static::$kernel->shutdown();

        if ($container instanceof ResetInterface) {
            $container->reset();
        }

        static::$kernel = null;
    }
}
