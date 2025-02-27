<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Il existe déjà un compte associé à cet email.')]
#[UniqueEntity(fields: ['username'], message: 'Ce nom d\'utilisateur est déjà pris.')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Email')]
    #[Assert\Email(message: 'Cet Email n\'est pas valide')]
    #[Assert\Regex(
        pattern: '/^[a-z0-9._%+-]+@campus-eni\.fr$/i',
        message: 'L\'email doit être une adresse campus (ex: name@campus-eni.fr).'
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Veuillez renseigner un role')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Nom')]
    #[Assert\Length(max: 50,maxMessage: 'Maximum 50 caractères')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ -]+$/',
        message: 'Le nom doit uniquement contenir des lettres, des espaces, et des tirets.'
    )]
    private ?string $lastname = null;
    
    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Veuillez renseigner un Prénom')]
    #[Assert\Length(max: 50, maxMessage: 'Maximum 50 caractères')]
    #[Assert\Regex(
        pattern: '/^[a-zA-ZÀ-ÿ -]+$/',
        message: 'Le prénom doit uniquement contenir des lettres, des espaces, et des tirets.'
    )]
    private ?string $firstname = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone n\'est pas valide')]
    #[Assert\Length(
        max: 10,
        maxMessage: 'Le numéro de téléphone ne peut pas contenir plus de {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^(0|\+33)[1-9](\d{8})$/',
        message: 'Le téléphone doit être au format français (ex: 0612345678 ou +33612345678).'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(max: 50, maxMessage: 'Maximum 50 caractères')]
    #[Assert\NotBlank(message: 'Veuillez renseigner un pseudo.')]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $img = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Un campus doit être attribué.')]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, NotifMessage>
     */
    #[ORM\ManyToMany(targetEntity: NotifMessage::class)]
    private Collection $messages;

    public function __construct(){
        $this->roles = ['ROLE_USER'];
        $this->messages = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getImg(): ?string
    {
        return $this->img;
    }

    public function setImg(?string $img): static
    {
        $this->img = $img;

        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, NotifMessage>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(NotifMessage $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
        }

        return $this;
    }

    public function removeMessage(NotifMessage $message): static
    {
        $this->messages->removeElement($message);

        return $this;
    }
}
