<?php

declare(strict_types=1);

namespace App\Security\Domain\Model;

use App\Transcode\Domain\Model\Transcode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'user_user', uniqueConstraints: [new ORM\UniqueConstraint(name: 'username', columns: ['username'])])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'string', nullable: false)]
    private string $id;

    #[ORM\Column(name: 'username', type: 'string', nullable: false)]
    private string $username;

    #[ORM\Column(name: 'password', type: 'string', length: 512, nullable: false)]
    private string $password;

    #[ORM\Column(name: 'loginFailureCounter', type: 'integer', nullable: false)]
    private int $loginFailureCounter;

    #[ORM\Column(name: 'roles', type: 'json', nullable: false)]
    private array $roles;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Transcode::class, cascade: ['persist'])]
    private Collection $transcodes;

    public function __construct(string $username, bool $isAdmin)
    {
        $this->id = Uuid::uuid4()->toString();
        $this->username = $username;
        $this->loginFailureCounter = 0;
        $this->roles = ['ROLE_USER'];
        if ($isAdmin) {
            $this->roles[] = "ROLE_ADMIN";
        }
        $this->transcodes = new ArrayCollection();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getLoginFailureCounter(): int
    {
        return $this->loginFailureCounter;
    }

    public function getRoles(): array
    {
        return $this->roles;

    }
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles);
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function resetFailureCounter(): void
    {
        $this->loginFailureCounter = 0;
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->getUsername();
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }
}
