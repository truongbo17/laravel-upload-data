<?php

namespace TruongBo\UploadToLaravel\Queue;

use Carbon\Carbon;
use TruongBo\UploadToLaravel\Enum\DataStatus;
use TruongBo\UploadToLaravel\Enum\UploadStatus;
use Illuminate\Support\Facades\DB;

class UploadDocumentQueue implements UploadQueueInterface
{
    protected static ?int $id;
    protected array $where;

    public function __construct(protected $model, bool $reload)
    {
        self::$id = null;
        $where = [UploadStatus::NO, UploadStatus::INIT];

        //Re-upload
        if ($reload) {
            $where = [
                UploadStatus::SUCCESS,
                UploadStatus::ERROR,
                UploadStatus::FAIL,
            ];
        }

        $this->where = $where;
    }

    public function hasPendingDataFile(): bool
    {
        return $this->model::whereIn('upload_status', $this->where)
            ->when(self::$id, function ($query) {
                $query->where('id', '<>', self::$id);
            })
            ->where(config('uploadtolaravel.check_data'), DataStatus::HAS_DATA)
            ->exists();
    }

    public function firstPendingDataFile()
    {
        return DB::transaction(function () {
            $first = $this->model::whereIn('upload_status', $this->where)
                ->when(self::$id, function ($query) {
                    $query->where('id', '<>', self::$id);
                })
                ->where(config('uploadtolaravel.check_data'), DataStatus::HAS_DATA)
                ->first();

            if ($first) {
                $first->update([
                    'upload_status' => UploadStatus::INIT,
                    'uploaded_at' => Carbon::now(),
                ]);

                $uploadDocument = [];
                foreach (config('uploadtolaravel.columns') as $column) {
                    $uploadDocument[$column] = $first->$column;
                }

                $this->setId($first->id);

                return $uploadDocument;
            }
            return null;
        });
    }

    public function setId(int $id)
    {
        self::$id = $id;
    }

    public function setStatus(int $status)
    {
        return $this->model::where('id', $this->getId())
            ->update(['upload_status' => $status]);
    }

    public function getId(): ?int
    {
        return self::$id;
    }
}
