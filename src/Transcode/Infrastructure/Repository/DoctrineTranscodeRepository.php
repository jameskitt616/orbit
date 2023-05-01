<?php

declare(strict_types=1);

namespace App\Transcode\Infrastructure\Repository;

use App\Security\Domain\Model\User;
use App\Transcode\Domain\Model\Transcode;
use App\Transcode\Domain\Repository\TranscodeRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineTranscodeRepository implements TranscodeRepository
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    public function findById(int $id): Transcode
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('transcode')
            ->from(Transcode::class, 'transcode')
            ->where('transcode.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getSingleResult();
    }

    public function findAllByUser(User $user): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('transcode')
            ->from(Transcode::class, 'transcode')
            ->where('transcode.ownedBy = :user')
            ->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }

    public function save(Transcode $transcode): void
    {
        $this->em->persist($transcode);
        $this->em->flush();
    }

    public function delete(Transcode $transcode): void
    {
        $this->em->remove($transcode);
        $this->em->flush();
    }
}
