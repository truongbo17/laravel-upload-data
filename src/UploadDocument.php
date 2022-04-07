<?php

namespace TruongBo\UploadToLaravel;

use TruongBo\UploadToLaravel\Libs\DiskPathTools\DiskPathInfo;
use TruongBo\UploadToLaravel\Enum\UploadStatus;
use TruongBo\UploadToLaravel\Queue\UploadDocumentQueue;
use Carbon\Carbon;
use Exception;
use GuzzleHttp;
use GuzzleHttp\Exception\GuzzleException;
use Vuh\CliEcho\CliEcho;

class UploadDocument
{
    protected static int $limit = 0;
    protected static int $successUpload = 0;
    protected static int $timeStart = 0; //time start upload
    protected int $timeLimit = 60; //the time that the host allows upload
    protected int $waitTime = 60; //wait time upload then upload max request in per minute

    public function __construct(protected string $host, protected string $token, protected int $limitInput, protected UploadDocumentQueue $queueUpload)
    {
        if (config()->has('uploadtolaravel.time_limit')) $this->timeLimit = config('uploadtolaravel.time_limit');
        if (config()->has('uploadtolaravel.wait_time')) $this->waitTime = config('uploadtolaravel.wait_time');

        self::$timeStart = Carbon::now()->timestamp;
    }

    public function run()
    {
        while ($this->queueUpload->hasPendingDataFile()) {

            if (!self::checkTime()) {
                CliEcho::warningnl("Waiting $this->waitTime second ...");
                //wait one minute after continues request to host
                $this->waitUpload();
            }

            $uploadDocument = $this->queueUpload->firstPendingDataFile();
            if (is_null($uploadDocument)) continue;

            if (config('uploadtolaravel.storage_data')) {
                $data = $this->getData($uploadDocument['data_file']);
                if (is_null($data)) return null;

                $data->referer_links = $uploadDocument['url']; //current url
                $data = $this->formatData($data); //format data
            } else {
                $data = $uploadDocument;
            }

            try {
                CliEcho::infonl("Upload data from URL : [" . $uploadDocument['url'] . "] to Host : [$this->host] - Time : " . Carbon::now()->toDateTimeString());

                $response = (new GuzzleHttp\Client())->post($this->host, [
                    'headers' => [
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->token
                    ],
                    GuzzleHttp\RequestOptions::JSON => $data
                ]);

                $res = $response->getBody()->getContents();
                dump($res);
                $code = $response->getStatusCode();

                if ($code == 200) {
                    $this->queueUpload->setStatus(UploadStatus::SUCCESS);
                    self::$successUpload++;
                } else {
                    $this->queueUpload->setStatus(UploadStatus::FAIL);
                }
            } catch (GuzzleException $exception) {
                CliEcho::errornl($exception->getMessage());
                $this->queueUpload->setStatus(UploadStatus::ERROR);
            }

            self::$limit++;
        }

        return self::$successUpload++;
    }

    public function checkTime()
    {
        if (Carbon::now()->timestamp - self::$timeStart < $this->timeLimit && self::$limit < $this->limitInput) {
            return true;
        }
        return false;
    }

    public function waitUpload()
    {
        //sleep time
        sleep($this->waitTime);

        //reset time and limit
        self::$timeStart = Carbon::now()->timestamp;
        self::$limit = 0;

        //refresh
        self::run();
    }

    public function getData(string $data_file)
    {
        $data_file = str_replace('"', '', $data_file); //fix ""data:000\/007\/7876.json:312""

        try {
            return json_decode(DiskPathInfo::parse($data_file)->read());
        } catch (Exception $e) {
            CliEcho::errornl("Fail : " . $e->getMessage());
        }
    }

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
    public function formatData(object $data): object
    {
        $data->title = (string)$data->title;
        $data->referer_links = (string)$data->referer_links;

        if (!is_string($data->abstract)) $data->abstract = "";
        if (!is_string($data->description)) $data->description = "";
        if (!is_string($data->download_link)) $data->download_link = "";

        $data->author = $this->cleanArray($data->author);
        $data->keywords = $this->cleanArray($data->keywords);

        return $data;
    }

    public function cleanArray($value): array
    {
        if ((is_string($value) && mb_strlen($value) > 0) || is_object($value)) {
            $value = (array)$value;
        } elseif ($value == "") {
            $value = [];
        }
        $value = array_filter($value);
        $value = preg_replace('/[#$%^&*()+=\-\[\]\';,.\/{}|":<>?~\\\\]/', '', $value);
        $value = array_map('trim', $value);

        return $value;
    }
}


