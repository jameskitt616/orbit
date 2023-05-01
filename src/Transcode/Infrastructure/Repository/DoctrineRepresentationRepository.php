<?php

declare(strict_types=1);

namespace App\Transcode\Infrastructure\Repository;

use App\Transcode\Domain\Model\Representation;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\RepresentationRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineRepresentationRepository implements RepresentationRepository
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    public function findById(int $id): Representation
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('representation')
            ->from(Representation::class, 'representation')
            ->where('representation.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getSingleResult();
    }

    public function findAll(): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('representation')
            ->from(Representation::class, 'representation');

        return $qb->getQuery()->getResult();
    }

    public function save(Representation $representation): void
    {
        $this->em->persist($representation);
        $this->em->flush();
    }

    public function delete(Representation $representation): void
    {
        $this->em->remove($representation);
        $this->em->flush();
    }
}
