<?php

return [
    /*
     * Document storage table
     */
    'table' => env('UPLOAD_TO_LARAVEL_TABLE', 'crawl_urls'),

    /*
     * Config upload command
     */
    'upload' => [
        'host' => env('UPLOAD_TO_LARAVEL_HOST', '127.0.0.1'),
        'token' => env('UPLOAD_TO_LARAVEL_TOKEN', ''),
    ],

    /*
     * Column check has data
     */
    'check_data' => 'data_status',

    /*
     * Select the column you want to upload
     */
    'columns' => [
        'site',
        'url',
        'data_file',
    ],

    /*
     * Save data in storage
     */
    'storage_data' => true,
    'data_file' => 'data_file',

    /*
     * Time upload
     * wait_time must be greater than or equal to time_limit
     */
    'time_limit' => 60, //The time that the host allows upload
    'wait_time' => 60, //Wait time upload then upload max request in per minute

    /*
   * Format data
   *
   * title : string
   * author : array
   * abstract : string
   * description : string
   * download_link : string
   * referer_links : string
   * keywords : array
  */
];