<?php


namespace App\Entity;

class ActionButton {
    private string $route;
    private string $name;
}

class PossibleActionManager
{
    private User $user;
    private Ticket $ticket;

    /**
     * PossibleActionManager constructor.
     * @param User $user
     * @param Ticket $ticket
     */
    public function __construct(User $user, Ticket $ticket)
    {
        $this->user = $user;
        $this->ticket = $ticket;
    }

    public function getActions() : array
    {
        return [
            '/delete' => 'Delete ticket',
            '/edit' => 'Edit ticket'
        ];
    }
}