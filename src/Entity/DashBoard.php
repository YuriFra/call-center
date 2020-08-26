<?php


namespace App\Entity;

use App\Repository\TicketRepository;

class DashBoard
{
    private int $counterOpen = 0;
    private int $counterClosed = 0;
    private int $counterReopened = 0;
    private int $percent = 0;

    /**
     * DashBoard constructor.
     * @param TicketRepository $ticketRepository
     */
    public function __construct(TicketRepository $ticketRepository)
    {
        $this->countOpen($ticketRepository);
        $this->countClosed($ticketRepository);
        $this->countReopened($ticketRepository);
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
     * @param int $counterOpen
     */
    public function setCounterOpen(int $counterOpen): void
    {
        $this->counterOpen = $counterOpen;
    }

    /**
     * @return int
     */
    public function getCounterClosed(): int
    {
        return $this->counterClosed;
    }

    /**
     * @param int $counterClosed
     */
    public function setCounterClosed(int $counterClosed): void
    {
        $this->counterClosed = $counterClosed;
    }

    /**
     * @return int
     */
    public function getCounterReopened(): int
    {
        return $this->counterReopened;
    }

    /**
     * @param int $counterReopened
     */
    public function setCounterReopened(int $counterReopened): void
    {
        $this->counterReopened = $counterReopened;
    }

    /**
     * @return int
     */
    public function getPercent(): int
    {
        return $this->percent;
    }

    /**
     * @param int $percent
     */
    public function setPercent(int $percent): void
    {
        $this->percent = $percent;
    }

    private function countOpen(TicketRepository $ticketRepository): void
    {
        $tickets = $ticketRepository->findAll();
        foreach ($tickets as $ticket) {
            if ($ticket->getStatus() === 'open') {
                ++$this->counterOpen;
            }
        }
    }

    private function countClosed(TicketRepository $ticketRepository): void
    {
        $tickets = $ticketRepository->findAll();
        foreach ($tickets as $ticket) {
            if ($ticket->getStatus() === 'closed') {
                ++$this->counterClosed;
            }
        }
    }

    private function countReopened(TicketRepository $ticketRepository): void
    {
        $tickets = $ticketRepository->findAll();
        foreach ($tickets as $ticket) {
            if ($ticket->getReopened() === true) {
                ++$this->counterReopened;
            }
        }
    }

    private function countPercent(): void
    {
        $this->percent = ($this->counterReopened * 100) / $this->counterClosed;
    }
}