<?php

namespace App\Controller;
use App\Entity\Permis;
use App\Form\PermisFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\PermisRepository;
// use Symfony\Component\HttpFoundation\RedirectResponse;


class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(PermisRepository $permisRepository): Response
    {  
        $permis = $permisRepository->findAll();
        return $this->render('home/home.html.twig', [
            'controller_name' => 'HomeController',
             'permis' => $permis,
        ]);
    }

    #[Route('/detailHome/{id}', name: 'app_detail')]
     public function detailHome(PermisRepository $permisRepository, $id): Response
     { 
         $permis = $permisRepository->find($id);
     
         if (!$permis) {
             throw $this->createNotFoundException('Permis not found for id ' . $id);
         }
     
         return $this->render('home/detail.html.twig', [
             'permis' => $permis,
         ]);
     }
    
    
     
}
