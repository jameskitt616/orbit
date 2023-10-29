<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Repository;

use App\Transcode\Domain\Model\Representation;

interface RepresentationRepository
{
    public function findById(int $id): Representation;

    /** @return Representation[] */
    public function findAll(): array;

    public function save(Representation $representation): void;

    public function delete(Representation $representation): void;
}
