<?php

namespace App\Controller;

use App\Service\Cart;
use App\Entity\Creneaux;
use App\Form\CreneauxType;
use App\Repository\UserRepository;
use App\Repository\CreneauxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/creneaux')]
class CreneauxController extends AbstractController
{   

    #[Route('/', name: 'app_creneaux_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository): Response
    {
       //récupération du moniteur et élève 
        $moniteur = $userRepository->getUsersByRole('ROLE_MONITEUR');
        $eleve = $userRepository->getUsersByRole('ROLE_ELEVE');
        // dd($moniteur);
        $creneaux = new Creneaux();
        $form = $this->createForm(CreneauxType::class, $creneaux, ["moniteur" => $moniteur]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($creneaux);
            $entityManager->flush();

            return $this->redirectToRoute('app_creneaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('creneaux/new.html.twig', [
            'creneaux' => $creneaux,
            'form' => $form,
        ]);
    }

    #[Route('/list', name: 'app_creneaux_index', methods: ['GET'])]
     public function index(CreneauxRepository $creneauxRepository): Response
     {
         $creneauxes = $creneauxRepository->findAll();
         return $this->render('creneaux/list.html.twig', [
             'creneauxes' => $creneauxes, //
         ]);
     }

    #[Route('/{id}', name: 'app_creneaux_show', methods: ['GET'])]
    public function show(Creneaux $creneaux): Response
    { 
        return $this->render('creneaux/show.html.twig', [
            'creneaux' => $creneaux,
        ]);
    }

    // #[Route('/{id}', name: 'app_creneaux_show', methods: ['GET'])]
    // public function show(Creneaux $creneaux = null): Response
    // {
    //     // Vérifie si l'entité Creneaux existe
    //     if (!$creneaux) {
    //         throw $this->createNotFoundException('Le créneau n\'existe pas.');
    //     }
    
    //     return $this->render('creneaux/show.html.twig', [
    //         'creneaux' => $creneaux,
    //     ]);
    // }

    

    #[Route('/{id}/edit', name: 'app_creneaux_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Creneaux $creneaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreneauxType::class, $creneaux);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_creneaux_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('creneaux/edit.html.twig', [
            'creneaux' => $creneaux,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_creneaux_delete')]
    public function delete(Request $request, Creneaux $creneaux, EntityManagerInterface $entityManager): Response
    {
        if ($creneaux) {
                    $entityManager->remove($creneaux);
                    $entityManager->flush();
                    $this->addFlash('success', 'Votre créneau est supprimé avec succès.');
                }
            return $this->redirectToRoute('app_creneaux_index', [], Response::HTTP_SEE_OTHER);
    
    }
      



    #[Route('/{id}/update', name: 'app_creneaux_update', methods: ['GET', 'POST'])]
    public function update(Request $request, Creneaux $creneaux, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CreneauxType::class, $creneaux);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
    
            // Ajoutez une instruction dump pour déboguer
            dump('Redirection effectuée');
    
            return $this->redirectToRoute('app_creneaux_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('creneaux/updateCreneaux.html.twig', [
            'creneaux' => $creneaux,
            'form' => $form,
        ]);
    }

   

    #[Route('/creneaux/validate', name: 'app_creneaux_validate', methods: ['GET'])]
    public function validateCreneaux(EntityManagerInterface $entityManager, Cart $cart): Response
    { 
        
        //récupèrent les détails du panier(des creneaux) à partir du service Cart
        $cartCreneaux = $cart->getDetails();
        //stoker les ces détails dans $creneaux
        $creneaux = $cartCreneaux['creneaux'];
        //La boucle itère sur chaque créneau présent dans le panier
        foreach($creneaux as $creneau){
        //stocker Chaque élément de $creneaux 
            // Vérifiez si la plage horaire est disponible
                // Mettez à jour la plage horaire comme réservée
                $creneau->setIsAvailable(false);
        
                // Définissez l'élève qui a réservé la plage horaire 
                $user = $this->getUser(); 
                //  Associe l'élève connecté au créneau
                $creneau->setUserEleve($user);
                // marquer le
                $entityManager->persist($creneau);  
        }
       //Enregistre tous les changements dans la base de données  
        $entityManager->flush();
        //Vide le panier après avoir validé les créneaux
        $cart->remove();
        //Ajoute un message flash de succès
        $this->addFlash('success', 'Vos créneaux sont validés.'); 
         
        // Redirigez vers la page de la liste ou toute autre page appropriée
        return $this->redirectToRoute('app_creneaux_index'); 
    }


    #[Route('/creneaux/{id}/cancel', name: 'app_cancel', methods: ['GET'])]
    public function cancel(Creneaux $creneaux, EntityManagerInterface $entityManager): Response
    {
        //vérifier si l'utilisateur a le role admin (seul autoriser àn annuler)
        if ($this->isGranted('ROLE_ADMIN')) {
            //mettre le creneau dispo
            $creneaux->setIsAvailable(true);
            //sUPPRIMER l'élève en le mettant null
            $creneaux->setUserEleve(null); 
            //mise à jour bdd
            $entityManager->flush();
    
            return $this->redirectToRoute('app_creneaux_index');
        }
    
        $this->addFlash('error', 'Vous n\'êtes pas autorisé à annuler cette réservation.');
    
        return $this->redirectToRoute('app_creneaux_index');
    }
}
