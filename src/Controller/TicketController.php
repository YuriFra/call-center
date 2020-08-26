<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ResponseCustomerType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/ticket")
 */
class TicketController extends AbstractController
{
    /**
     * @Route("/", name="ticket_index", methods={"GET"})
     */
    public function index(TicketRepository $ticketRepository, UserInterface $userInterface, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['username' => $userInterface->getUsername()]);
        $allTickets=$ticketRepository->findAll();
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

            $tickets = $ticketRepository->findBy(['user' => $user->getId()]);
        }



        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
            'user'=>$user,
            'statuses'=>Ticket::status,
            'roles'=>User::roles,
        ]);
    }

    /**
     * @Route("/new", name="ticket_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserInterface $userInterface, UserRepository $userRepository): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user=$userRepository->findOneBy(["username"=>$userInterface->getUsername()]);
            $ticket->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($ticket);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/new.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_show", methods={"GET"})
     */
    public function show(Ticket $ticket): Response
    {

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'roles'=>User::roles,
        ]);
    }

    /**
     * @Route("/{id}/close", name="ticket_close", methods={"GET"})
     */
    public function close(Ticket $ticket): Response
    {
        $ticket->setStatus(Ticket::status['closed']);
        $ticket->setClosed(new DateTime());
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/escalate", name="ticket_escalate", methods={"GET", "POST"})
     */
    public function escalate(Ticket $ticket, UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->findAll();

        //@todo:  vraag koen $request->request->get('premiumAgents')
        if($request->getMethod()=='POST'){
            $ticket->setAgentId($request->request->get('premiumAgents'));
            $ticket->setEscalated(true);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ticket_index');
        }
        return $this->render('ticket/escalate.html.twig', [
            'users' => $users,
            'roles'=>User::roles,
        ]);
    }

    /**
     * @Route("/{id}/reopen", name="ticket_reopen", methods={"GET"})
     */
    public function reopen(Ticket $ticket): Response
    {
        $ticket->setStatus(Ticket::status["open"]);
        $ticket->setClosed(NULL);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/comment", name="ticket_comment", methods={"GET","POST"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @param UserInterface $userInterface
     * @param Ticket $ticket
     * @return Response
     */
    public function addComment(Request $request, UserRepository $userRepository, UserInterface $userInterface, Ticket $ticket): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $user = $userRepository->findOneBy(['username' => $userInterface->getUsername()]);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $comment->setUser($user);
            $comment->setTicket($ticket);
            if (in_array(User::roles["FLA"], $userInterface->getRoles())) {
                $comment->setPrivate($data->getPrivate());
                if ($ticket->getStatus() === Ticket::status['in progress']) {
                    $ticket->setStatus(Ticket::status['Waiting for customer feedback']);
                }
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_show',['id'=>$ticket->getId()]);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ticket_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/edit.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/respond", name="ticket_respond", methods={"GET","POST"})
     * @param Request $request
     * @param Ticket $ticket
     * @return Response
     */
    public function respond(Request $request, Ticket $ticket): Response
    {
        $form = $this->createForm(ResponseCustomerType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setStatus(Ticket::status['in progress']);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/agentMail.html.twig', [
            'ticket' => $ticket,
             'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Ticket $ticket): Response
    {

        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/{user_id}", name="ticket_assign", methods={"GET"})
     */
    public function assignTicket(Ticket $ticket, UserInterface $userInterface, UserRepository $userRepository): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $user=$userRepository->findOneBy(["username"=>$userInterface->getUsername()]);
        $ticket->setStatus(Ticket::status['in progress']);
        $ticket->setAgentId($user->getId());
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }

}
