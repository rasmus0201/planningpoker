<?php

declare(strict_types = 1);

namespace App;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class Application extends Container
{
    private string $basePath = '';
    private bool $booted = false;
    private array $loadedConfigurations = [];
    private array $loadedProviders = [];

    public function __construct(string $basePath = null)
    {
        $this->basePath = $basePath;

        $this->bootstrapContainer();
    }

    public function path(): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app';
    }

    public function runningInConsole(): bool
    {
        return \PHP_SAPI === 'cli' || \PHP_SAPI === 'phpdbg';
    }

    public function environment()
    {
        $env = env('APP_ENV', 'production');

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }

            return false;
        }

        return $env;
    }

    public function boot(): void
    {
        $this->registerDatabaseBindings();
    }

    /**
     * Register a service provider with the application.
     *
     * @param  \Illuminate\Support\ServiceProvider|string  $provider
     * @return void
     */
    public function register($provider): void
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    public function basePath(string $path = null): string
    {
        if (isset($this->basePath)) {
            return $this->basePath . ($path ? '/' . $path : $path);
        }

        if ($this->runningInConsole()) {
            $this->basePath = getcwd();
        } else {
            $this->basePath = realpath(getcwd() . '/../');
        }

        return $this->basePath($path);
    }

    public function getConfigurationPath(string $name = null): string
    {
        if (!$name) {
            $appConfigDir = $this->basePath('config') . '/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            }
        } else {
            $appConfigPath = $this->basePath('config') . '/' . $name . '.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            }
        }
    }

    public function configure(string $name): void
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->make('config')->set($name, require $path);
        }
    }

    protected function bootstrapContainer(): void
    {
        static::setInstance($this);

        $this->instance('app', $this);
        $this->instance(self::class, $this);

        $this->instance('path', $this->path());

        $this->instance('env', $this->environment());

        $this->registerContainerAliases();
        $this->registerConfigBindings();
    }

    protected function registerContainerAliases(): void
    {
        $this->aliases = [
            \Illuminate\Contracts\Foundation\Application::class => 'app',
            \Illuminate\Contracts\Config\Repository::class => 'config',
            \Illuminate\Container\Container::class => 'app',
            \Illuminate\Contracts\Container\Container::class => 'app',
            \Illuminate\Database\ConnectionResolverInterface::class => 'db',
            \Illuminate\Database\DatabaseManager::class => 'db',
            Capsule::class => 'db',
            \Illuminate\Database\Connection::class => 'db.connection',
            \Psr\Log\LoggerInterface::class => 'log',
        ];
    }

    protected function registerConfigBindings(): void
    {
        $this->singleton('config', function () {
            return new ConfigRepository();
        });
    }

    protected function registerDatabaseBindings(): void
    {
        $capsule = new Capsule();

        $connection = config('database.default');

        $capsule->addConnection(config('database.connections.' . $connection));

        $capsule->setAsGlobal();

        $capsule->bootEloquent();

        $this->singleton('db', function() use ($capsule) {
            return $capsule;
        });

        $this->singleton('db.connection', function () use ($capsule) {
            $connection = $capsule->getConnection();
            $connection->useDefaultSchemaGrammar();

            return $connection;
        });
    }
}
