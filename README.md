## UploadToLaravel

### Description

* Upload document from database or storage =))

### Installation

* The library can be installed via Composer:

```
composer require truongbo/uploadtolaravel
```

### Configuration

* After UploadToLaravel has been installed, publish its configuration file using:

```
php artisan vendor:publish --provider="TruongBo\UploadToLaravel\UploadToLaravelServiceProvider"
```

* Config in `config/uploadtolaravel.php` :
    * Document storage table , two columns upload_status and uploaded_at will be added to this table
    ``` 
    'table' => env('UPLOAD_TO_LARAVEL_TABLE', 'crawl_urls')
    ```
  
    * Config host and token upload
    ``` 
    'upload' => [
        'host' => env('UPLOAD_TO_LARAVEL_HOST', '127.0.0.1'),
        'token' => env('UPLOAD_TO_LARAVEL_TOKEN', ''),
    ],
    ```
  
    * You should have a column to check if there is data
    ``` 
    'check_data' => 'data_status',
    ```
  
    * Select the columns where you want it to be uploaded
    ```
    'columns' => [
        'site',
        'url',
        'data_file',
    ],
    ```
  
    * If you store data in storage, set it to true
    ```
    'storage_data' => true
    ```
  
    **Note** :  You need to specify the column to save data_file and don't forget to add this column to the config columns above
    ```
    'data_file' => 'data_file'
    ```
  
    * Time upload (wait_time must be greater than or equal to time_limit)
    ```
    'time_limit' => 60, //The time that the host allows upload
    'wait_time' => 60, //Wait time upload then upload max request in per minute
    ```

* Finally, don't forget to run Laravel database migrations to add second columns upload_status and uploaded_at
```
php artisan migrate
```

### Usage
* Run command : 
```
php artisan upload:auto "App\Models\CrawlUrl" --limit=100 
```
  * `"App\Models\CrawlUrl"` : Your model want upload
  * limit : Limit upload to host
  * Add option --reload if you want to reupload
  * You can add option host and token if you don't config it in `config/uploadtolaravel.php`