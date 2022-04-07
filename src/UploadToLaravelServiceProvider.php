<?php

namespace TruongBo\UploadToLaravel;

use Illuminate\Support\ServiceProvider;
use TruongBo\UploadToLaravel\Console\Commands\UploadDocuments;

class UploadToLaravelServiceProvider extends ServiceProvider
{
    /**
     * @var string
     */
    private $configPath;

    /**
     * @var string
     */
    private $migrationsPath;

    /**
     * @var array
     */
    private $commands = [
        UploadDocuments::class
    ];

    public function __construct($app)
    {
        parent::__construct($app);

        $this->configPath = dirname(__DIR__) . '/config/uploadtolaravel.php';
        $this->migrationsPath = dirname(__DIR__) . '/database/migrations';
    }

    public function boot()
    {
        $this->publishes([
            $this->configPath => config_path(basename($this->configPath)),
        ]);

        $this->loadMigrationsFrom($this->migrationsPath);

        $this->commands($this->commands);
    }

    public function register()
    {
        $this->mergeConfigFrom(
            $this->configPath,
            basename($this->configPath, '.php')
        );
    }
}

?>