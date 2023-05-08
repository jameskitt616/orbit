<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Repository;

use App\Security\Domain\Model\User;
use App\Transcode\Domain\Model\Transcode;

interface TranscodeRepository
{
    /**
     * @param string $id
     *
     * @return Transcode
     */
    public function findById(string $id): Transcode;

    /**
     * @param User $user
     *
     * @return array
     */
    public function findAllByUser(User $user): array;


    /**
     * @param Transcode $transcode
     */
    public function save(Transcode $transcode): void;

    /**
     * @param Transcode $transcode
     */
    public function delete(Transcode $transcode): void;
}
