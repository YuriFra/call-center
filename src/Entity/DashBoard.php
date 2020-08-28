<?php


namespace App\Entity;

use App\Repository\TicketRepository;

class DashBoard
{
    private int $counterOpen = 0;
    private int $counterClosed = 0;
    private int $counterReopened = 0;
    private float $percent = 0;

    /**
     * DashBoard constructor.
     * @param TicketRepository $ticketRepository
     */
    public function __construct(TicketRepository $ticketRepository)
    {
        $tickets = $ticketRepository->findAll();
        $this->countOpen($tickets);
        $this->countClosed($tickets);
        $this->countReopened($tickets);
        $this->countPercent();
    }

    /**
     * @return int
     */
    public function getCounterOpen(): int
    {
        return $this->counterOpen;
    }

    /**
     * @return int
     */
    public function getCounterClosed(): int
    {
        return $this->counterClosed;
    }

    /**
     * @return int
     */
    public function getCounterReopened(): int
    {
        return $this->counterReopened;
    }

    /**
     * @return int
     */
    public function getPercent(): int
    {
        return $this->percent;
    }

    /**
     * @param Ticket[] $tickets
     */
    private function countOpen(array $tickets): void
    {

        foreach ($tickets as $ticket) {
            if ($ticket->getStatus() === 'open') {
                ++$this->counterOpen;
            }
        }
    }

    /**
     * @param Ticket[] $tickets
     */
    private function countClosed(array $tickets): void
    {
        foreach ($tickets as $ticket) {
            if ($ticket->getStatus() === 'closed') {
                ++$this->counterClosed;
            }
        }
    }

    /**
     * @param Ticket[] $tickets
     */
    private function countReopened(array $tickets): void
    {
        foreach ($tickets as $ticket) {
            if ($ticket->getReopened() === true) {
                ++$this->counterReopened;
            }
        }
    }

    private function countPercent(): void
    {
        if ($this->counterClosed + $this->counterReopened === 0) {
            $this->percent = 0;
        } else {
            $this->percent = ($this->counterReopened * 100) / ($this->counterClosed + $this->counterReopened);
        }
    }
}