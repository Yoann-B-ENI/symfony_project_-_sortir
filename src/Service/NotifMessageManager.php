<?php

namespace App\Service;

use App\Entity\NotifMessage;
use App\Entity\User;
use App\Repository\NotifMessageRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class NotifMessageManager
{
    private NotifMessageRepository $notifrepo;
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(NotifMessageRepository $notifRepo, EntityManagerInterface $em, Security $security){
        $this->notifrepo = $notifRepo;
        $this->em = $em;
        $this->security = $security;
    }

    /**
     * Just a shortcut for $user->getMessages()
     * @param \User|\UserInterface $user <p>
     * An Entity/User or a this->getUser()
     * </p>
     * @return Collection
     */
    public function getMessagesByUser(User|UserInterface $user): Collection
    {
        return $user->getMessages();
    }

    /**
     * Just a shortcut for getPublicMessagesByRoles($user->getRoles()
     *
     * $user can be an Entity/User or a $this->getUser()
     * @param \User|\UserInterface $user <p>
     * An Entity/User or a this->getUser()
     * </p>
     * @return mixed
     */
    public function getPublicMessagesByRolesOfUser(User|UserInterface $user): mixed
    {
        return $this->getPublicMessagesByRoles($user->getRoles());
    }

    /**
     * Just turns the $roles array into a string and calls
     * the Repository->findApprovedPublicMessages($rolesAsString)
     * @param array $roles <p>
     * The role(s) in array of string form
     * </p>
     * @return mixed
     */
    public function getPublicMessagesByRoles(array $roles): mixed
    {
        $rolesAsString = "['" . implode(", ", $roles) . "']";
        return $this->notifrepo->findApprovedPublicMessages($rolesAsString);
    }

    /**
     * Creates a simple NotifMessage, sends it to DB, returns it
     *
     * Built with a 'true' flag, a ROLE_USER role, and no target user
     * @param string $content The content of the message
     * @return NotifMessage
     */
    public function createSimpleMessage(string $content): NotifMessage
    {
        return $this->createMessage(content: $content, isFlagged: true, roles: ['ROLE_USER'], targetUser: null);
    }

    /**
     * Shortcut to createSimpleMessageToLoggedUser
     * @return NotifMessage
     */
    public function createSimpleMessageToSelf(string $content): NotifMessage
    {
        return $this->createSimpleMessageToLoggedUser($content);
    }

    /**
     * Creates a simple NotifMessage, sends it to DB, returns it
     *
     * Grabs the logged-in user and makes a message with a true flag and the user's roles
     * @param string $content The content of the message
     * @return NotifMessage
     */
    public function createSimpleMessageToLoggedUser(string $content): NotifMessage
    {
        $user = $this->security->getUser();
        //dd($user->getRoles());
        return $this->createMessage(content: $content, isFlagged: true, roles: $user->getRoles(), targetUser: $user);
    }

    /**
     * Creates a NotifMessage, sends it to DB, returns it
     *
     * If a $targetUser is entered, adds the msg to the user, down to DB.
     *
     * $user can be an Entity/User or a this->getUser()
     * @param string $content The content of the message
     * @param bool $isFlagged True for flagged (=unread, important, ...)
     * @param array $roles Array of string format
     * @param \User|\UserInterface|null $targetUser An Entity/User, a $this->getUser() or null for no target
     * @return NotifMessage
     */
    public function createMessage(string $content, bool $isFlagged, array $roles, User|UserInterface|null $targetUser): NotifMessage
    {
        $rolesAsString = "['ROLE_USER']";
        if (in_array('ROLE_ADMIN', $roles)){
            $rolesAsString = "['ROLE_ADMIN']"; // replace
        }

        $temp = new NotifMessage();
        $temp->setCreatedAt(new \DateTimeImmutable())
            ->setMessage($content)
            ->setIsFlagged($isFlagged)
            ->setRoles($rolesAsString)
        ;
        if ($targetUser){$targetUser->addMessage($temp);}
        $this->em->persist($temp);
        $this->em->flush();

        return $temp;
    }





}