<?php

declare(strict_types=1);

namespace App\Transcode\Domain\Repository;

use App\Transcode\Domain\Model\Representation;

interface RepresentationRepository
{
    /**
     * @param int $id
     *
     * @return Representation
     */
    public function findById(int $id): Representation;

    /**
     * @return array
     */
    public function findAll(): array;

    /**
     * @param Representation $representation
     */
    public function save(Representation $representation): void;

    /**
     * @param Representation $representation
     */
    public function delete(Representation $representation): void;
}
