<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Table('user')]
#[ORM\Entity]
#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(type
        : 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private readonly int $id;

    #[ORM\Column(type: 'string', length: 25, unique: true)]
    #[Assert\NotBlank(message: "Vous devez saisir un nom d'utilisateur.")]
    private ?string $username = null;

    #[ORM\Column(type: 'string', length: 64)]
    private ?string $password = null;

    /** @var array<int,string> */
    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string', length: 60, unique: true)]
    #[Assert\NotBlank(message: 'Vous devez saisir une adresse email.')]
    #[Assert\Email(message: "Le format de l'adresse n'est pas correcte.")]
    private ?string $email = null;

    #[ORM\OneToMany(targetEntity: Task::class, mappedBy: 'user')]
    private Collection $tasks;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function getId() : ?int
    {
        return $this->id;
    }

    public function getUsername() : ?string
    {
        return $this->username;
    }

    public function setUsername($username) : self
    {
        $this->username = $username;
        return $this;
    }

    public function getPassword() : ?string
    {
        return $this->password;
    }

    public function setPassword($password) : self
    {
        $this->password = $password;
        return $this;
    }

    public function getEmail() : ?string
    {
        return $this->email;
    }

    public function setEmail($email) : self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @see UserInterface
     *
     * @return array<int,string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array<int,string> $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole(string $role) : self
    {
        $this->roles = array($role);
        return $this;
    }

    public function eraseCredentials()
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
            $task->setUser($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getUser() === $this) {
                $task->setUser(null);
            }
        }

        return $this;
    }
}
