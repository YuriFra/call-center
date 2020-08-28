<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\DashBoard;
use App\Entity\Ticket;
use App\Entity\User;
use App\Form\CommentType;
use App\Form\ResponseCustomerType;
use App\Form\TicketType;
use App\Repository\CommentRepository;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/ticket")
 */
class TicketController extends AbstractController
{
    /**
     * @Route("/", name="ticket_index", methods={"GET"})
     * @param TicketRepository $ticketRepository
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(TicketRepository $ticketRepository): Response
    {
        $user = $this->getUser();
        $tickets=$ticketRepository->showTickets($user);
        $dashBoard = new DashBoard($ticketRepository);
        /*usort($tickets, function ($ticket1, $ticket2){
            $pos_a = array_search($ticket1->getPriority(), Ticket::priorities, true);
            $pos_b = array_search($ticket2->getPriority(), Ticket::priorities, true);
            return $pos_b-$pos_a;
        });*/

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
            'user'=>$user,
            'statuses'=>Ticket::status,
            'roles'=>User::roles,
            'dashboard' => $dashBoard,
        ]);
    }

    /**
     * @Route("/new", name="ticket_new", methods={"GET","POST"})
     * @param Request $request
     * @return Response
     */
    public function new(Request $request): Response
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user=$this->getUser();
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
     * @Route("/deAssign", name="ticket_deAssign", methods={"GET","POST"})
     * @Security("is_granted('ROLE_MANAGER')")
     * @param TicketRepository $ticketRepository
     * @return Response
     */
    public function deAssignAll(TicketRepository $ticketRepository): Response
    {
        $tickets=$ticketRepository->findAll();
        foreach ($tickets as $ticket){
           $ticket->setStatus(Ticket::status['open']);
           $ticket->setAgentId(NULL);
           $this->getDoctrine()->getManager()->flush();
        }
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}", name="ticket_show", methods={"GET"})
     * @param Ticket $ticket
     * @return Response
     */
    public function show(Ticket $ticket): Response
    {
        // if($this->getUser()->getId() !== $ticket->getUser()->getid()) {
           // throw new Exception('404');
        //}

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'roles'=>User::roles,
        ]);
    }

    /**
     * @Route("/{id}/close", name="ticket_close", methods={"GET"})
     * @param Ticket $ticket
     * @return Response
     */
    public function close(Ticket $ticket): Response
    {
        $ticket->setStatus(Ticket::status['closed']);
        $ticket->setClosed(new \DateTimeImmutable());
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/escalate", name="ticket_escalate", methods={"GET", "POST"})
     * @param Ticket $ticket
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function escalate(Ticket $ticket, UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->findAll();
        $loggedInUser=$this->getUser();
        if($request->request->get('premiumAgents')){
            $ticket->setAgentId($request->request->get('premiumAgents'));
            $ticket->setEscalated(true);
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ticket_index');
        }
        return $this->render('ticket/escalate.html.twig', [
            'users' => $users,
            'roles'=>User::roles,
            'loggedInUser'=>$loggedInUser
        ]);
    }

    /**
     * @Route("/{id}/reassign", name="ticket_reassign", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_MANAGER')")
     * @param Ticket $ticket
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function reassign(Ticket $ticket, UserRepository $userRepository, Request $request): Response
    {
        $users = $userRepository->findAll();//@todo filter here
        $user = $userRepository->findOneBy(['id' => $ticket->getAgentId()]);

        if($request->request->get('agents')){
            $ticket->setAgentId($request->request->get('agents'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ticket_index');
        }
        return $this->render('ticket/reassign.html.twig', [
            'users' => $users,
            'user' => $user,
            'roles'=>User::roles,
            'ticket' => $ticket,
        ]);
    }

    /**
     * @Route("/{id}/wontfix", name="ticket_wontfix", methods={"GET", "POST"})
     * @Security("is_granted('ROLE_MANAGER')")
     * @param Ticket $ticket
     * @param Request $request
     * @return Response
     */
    public function wontfix(Ticket $ticket, Request $request): Response
    {
        if($request->request->get('wontfixReason')){
            $ticket->setStatus(Ticket::status["closed"]);
            $ticket->setClosed(new \DateTimeImmutable());
            $ticket->setWontFix($request->request->get('wontfixReason'));
            $comment = new Comment();// Comment::create($user, $ticket, $text);
            $user = $this->getUser();
            $comment->setComment($request->request->get('wontfixReason'));
            $comment->setUser($user);
            $comment->setTicket($ticket);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();
            return $this->redirectToRoute('ticket_index');
        }
        return $this->render('ticket/wontfix.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * @Route("/{id}/reopen", name="ticket_reopen", methods={"GET"})
     * @param Ticket $ticket
     * @return Response
     */
    public function reopen(Ticket $ticket): Response
    {
        $ticket->setStatus(Ticket::status["in progress"]);
        $ticket->setClosed(NULL);
        $ticket->setReopened(true);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/comment", name="ticket_comment", methods={"GET","POST"})
     * @param Request $request
     * @param Ticket $ticket
     * @return Response
     */
    public function addComment(Request $request, Ticket $ticket): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        $user = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setCommentProperties($form,$user,$ticket);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('ticket_show',['id'=>$ticket->getId()]);
        }

        return $this->render('comment/new.html.twig', [
            'comment' => $comment,
            'form' => $form->createView(),
            'user' => $user,
            'roles' => User::roles,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="ticket_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Ticket $ticket
     * @return Response
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
     * @Route("/{id}/priority", name="ticket_priority", methods={"GET","POST"})
     * @Security("is_granted('ROLE_MANAGER')")
     * @param Request $request
     * @param Ticket $ticket
     * @return Response
     */
    public function priority(Request $request, Ticket $ticket): Response
    {
        if ($request->request->get('priorities')) {
            $ticket->setPriority($request->request->get('priorities'));
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('ticket_index');
        }

        return $this->render('ticket/priority.html.twig', [
            'ticket' => $ticket,
            'priorities'=>Ticket::priorities,
        ]);
    }

    /**
     * @Route("/{id}", name="ticket_delete", methods={"DELETE"})
     * @param Request $request
     * @param Ticket $ticket
     * @return Response
     */
    public function delete(Request $request, Ticket $ticket): Response
    {

        if ($this->isCsrfTokenValid('delete'.$ticket->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $comments= $ticket->getComments();
            foreach ($comments as $comment){
                $entityManager->remove($comment);
            }

            $entityManager->remove($ticket);
            $entityManager->flush();
        }

        return $this->redirectToRoute('ticket_index');
    }

    /**
     * @Route("/{id}/{user_id}", name="ticket_assign", methods={"GET"})
     * @param Ticket $ticket
     * @return RedirectResponse
     */
    public function assignTicket(Ticket $ticket): RedirectResponse
    {
        $user=$this->getUser();
        $ticket->setStatus(Ticket::status['in progress']);
        $ticket->setAgentId($user->getId());
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('ticket_index');
    }
}
