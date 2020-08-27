<?php

namespace App\Entity;

use App\Form\CommentType;
use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=CommentRepository::class)
 */
class Comment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $private;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Ticket::class, inversedBy="comments")
     *  @ORM\JoinColumn(nullable=false)
     */
    private $ticket;

    /**
     * Comment constructor.
     */
    public function __construct()
    {
        $this->private = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): self
    {
        $this->ticket = $ticket;

        return $this;
    }


    //@todo refactor
    public function setCommentProperties(Form $form, User $user, Ticket $ticket, UserInterface $userInterface): void
    {
        $data = $form->getData();
        $this->setUser($user);
        $this->setTicket($ticket);

        if (in_array(User::roles["FLA"], $userInterface->getRoles())) {
            $this->setPrivate($data->getPrivate());

            if ($ticket->getStatus() === Ticket::status['in progress'] && $data->getPrivate() === false) {
                $ticket->setStatus(Ticket::status['Waiting for customer feedback']);
            }
        }
    }
}
