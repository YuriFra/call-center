<?php

namespace App\Repository;

use App\Entity\Ticket;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    // /**
    //  * @return Ticket[] Returns an array of Ticket objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ticket
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function showTickets(UserInterface $userInterface, User $user){

        $allTickets= $this->findAll();

       //@todo: switch koen
        if(in_array(User::roles['MANAGER'], $userInterface->getRoles())){
            $tickets=$allTickets;
        } elseif(in_array(User::roles['SLA'], $userInterface->getRoles())){
            $tickets=[];
            foreach ($allTickets as $ticket) {
                if ($ticket->getAgentId() === $user->getId()) {
                    $tickets[] = $ticket;
                }
            }
        }  elseif (in_array(User::roles['FLA'], $userInterface->getRoles())) {
            $tickets=[];
            foreach ($allTickets as $ticket){
                if($ticket->getAgentId()===null || $ticket->getAgentId()===$user->getId()){
                    $tickets[]=$ticket;
                }
            }

        } else{

            $tickets = $this->findBy(['user' => $user->getId()]);
        }

        return $tickets;

    }
}
