<?php

namespace TruongBo\UploadToLaravel\Queue;

interface UploadQueueInterface
{
    public function hasPendingDataFile(): bool;

    public function firstPendingDataFile();

    public function setId(int $id);

    public function getId();

    public function setStatus(int $status);
}
