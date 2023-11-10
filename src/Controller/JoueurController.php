<?php

namespace App\Controller;

use App\Entity\Vote;
use App\Form\VoteType;
use App\Repository\JoueurRepository;
use App\Repository\VoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class JoueurController extends AbstractController
{
    #[Route('/joueurs', name: 'app_joueur')]
    public function index(JoueurRepository $joueurRepo): Response
    {
        $joueurs = $joueurRepo->findAll();
        return $this->render('joueur/index.html.twig', [
            'controller_name' => 'JoueurController',
            'joueurs' => $joueurs,
        ]);
    }

    #[Route('/addvote/{id}', name: 'app_joueur_vote')]
     public function addVote(JoueurRepository $joueurRepo, int $id,Request $request,VoteRepository $voteRepo): Response
     {
         $form = $this->createForm(VoteType::class);
         $form->get('joueur')->setData($joueurRepo->find($id));

            $form->handleRequest($request);



            if ($form->isSubmitted()) {
                $vote = new Vote();
                $vote->setJoueur($joueurRepo->find($id));
                $vote->setNoteVote($form->get('noteVote')->getData());
                $vote->setDate(new \DateTimeImmutable());
                $em = $this->getDoctrine()->getManager();
                $em->persist($vote);
                $em->flush();
                $joueur = $joueurRepo->find($id);
                $joueur->setMoyenneVote($voteRepo->getSommeVoteByJoueur($id)/$voteRepo->getCountVoteByJoueur($id));
                $em->persist($joueur);
                $em->flush();
                return $this->redirectToRoute('app_joueur');
            }
         return $this->render('vote/ajoutvote.html..twig', [
             'controller_name' => 'JoueurController',
             'form' => $form->createView(),
         ]);
     }

        #[Route('/joueur/{id}', name: 'app_joueur_show')]
        public function show(VoteRepository $voteRepo,JoueurRepository $joueurRepository, int $id): Response
        {
            $joueur = $joueurRepository->find($id);
            $votes = $voteRepo->getVoteByJoueur($id);
            return $this->render('joueur/show.html.twig', [
                'controller_name' => 'JoueurController',
                'votes' => $votes,
                'joueur' => $joueur,
            ]);
        }

        #[Route('/joueur/{id}/delete', name: 'app_joueur_delete')]
        public function delete(JoueurRepository $joueurRepository, int $id): Response
        {
            $joueur = $joueurRepository->find($id);
            $em = $this->getDoctrine()->getManager();
            $em->remove($joueur);
            $em->flush();
            return $this->redirectToRoute('app_joueur');
        }






}
