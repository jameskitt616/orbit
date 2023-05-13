<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Repository;

use App\Security\Domain\Model\User;
use App\Transcode\Domain\Model\Transcode;

interface TranscodeRepository
{
    public function findById(string $id): Transcode;

    /** @return Transcode[] */
    public function findAllByUser(User $user): array;

    public function save(Transcode $transcode): void;

    public function delete(Transcode $transcode): void;
}
