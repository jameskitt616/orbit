<?php

declare(strict_types=1);

namespace App\Security\Infrastructure\Doctrine;

use App\Security\Domain\Model\User;
use App\Security\Domain\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class DoctrineUserRepository implements UserRepository
{
    public function __construct(
        private EntityManagerInterface $em
    )
    {
    }

    public function findById(int $id): User
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('user')
            ->from(User::class, 'user')
            ->where('user.id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getSingleResult();
    }

    public function findByUsername(string $username): ?User
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('user')
            ->from(User::class, 'user')
            ->where('user.username = :username')
            ->setParameter('username', $username);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAll(): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('user')
            ->from(User::class, 'user');

        return $qb->getQuery()->getResult();
    }

    public function save(User $user): void
    {
        $this->em->persist($user);
        $this->em->flush();
    }

    public function delete(User $user): void
    {
        $this->em->remove($user);
        $this->em->flush();
    }
}
