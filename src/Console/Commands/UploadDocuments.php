<?php

namespace TruongBo\UploadToLaravel\Console;

use TruongBo\UploadToLaravel\Queue\UploadDocumentQueue;
use TruongBo\UploadToLaravel\UploadDocument;
use Illuminate\Console\Command;

class UploadDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:auto
    {model : Class name of model to Store Documents}
    {--host= : Host upload}
    {--token= : Token auth}
    {--limit= : Limit document upload}
    {--reload : Re-upload already uploaded documents }
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload documents to host laravel for Crawl';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //New Model
        $class = $this->argument('model');
        $model = new $class;

        //Option upload
        $host = $this->option('host') ?? config('uploadtolaravel.upload.host');
        $token = $this->option('token') ?? config('uploadtolaravel.upload.token'); //Currently not using token
        $limit = $this->option('limit') ?? 60; // limit request to one minute
        $reload = $this->option('reload');

        $this->info("Start upload from model : $class to : $host !!!");

        $uploadDocument = new UploadDocument($host, $token, $limit, new UploadDocumentQueue($model, $reload));
        $countUpload = $uploadDocument->run();

        $this->info("Upload $countUpload document success !!!");

        return self::SUCCESS;
    }
}
