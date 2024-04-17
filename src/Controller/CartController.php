<?php

namespace App\Controller;

use App\Service\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
// use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    /**
     * Récupère un panier détaillé contenant des objets Products et les totaux de quantité et de prix 
     * 
     * @param Cart $cart
     * @return Response
     */
    #[Route('/mon-panier', name: 'app_cart')]
    public function index(Cart $cart): Response
    {
        $cartCreneaux = $cart->getDetails();
        // dd($cartCreneaux);
        // $session->remove('nb');
        $vide = (empty ($cartCreneaux['creneaux'])) ? "Votre panier est vide" : "";
        return $this->render('cart/cart.html.twig', [
            'cart' => $cartCreneaux['creneaux'] ?? [],
            'totalQuantity' => $cartCreneaux['totals']['quantity'] ?? 0,
            'totalPrice' => $cartCreneaux['totals']['price'] ?? 0,
            'vide' => $vide,
        ]);
    }

    /**
     * Ajoute un article au panier (id du produit) et incrémente la quantitée (voir classe Cart)
     * @param Cart $cart
     * @param int $id
     * @return Response
     */
    #[Route('/panier/ajouter/{id}', name: 'add_to_cart')]
    public function add(Cart $cart, int $id, SessionInterface $session): Response
    {
        // $booleanResult = $cart->addToCart($id);
        $cart->addToCart($id);
        $cartDetails = $cart->getDetails();
        $nb = 0;
        //foreach ($cartDetails['totals']['quantity'] as $value) {
            $nb += $cartDetails['totals']['quantity'];
        //}
        $session->set('nb', $nb);

        
        $message = "Vous avez bien réservé ce creneaux.";
      
        return $this->json(["nb" => $nb, 'message' => $message]);
    }

    /**
     * Réduit de 1 la quantité pour un article du panier
     * @param Cart $cart
     * @param int $id
     * @return Response
     */
   

    #[Route('/panier/remove/{id}', name: 'remove_item')]
    public function removeItem(Cart $cart, int $id): Response
    {
        $cart->removeItem($id);
        return $this->redirectToRoute('app_cart');
    }


    /**
     * Vide le panier entièrement
     *
     * @param Cart $cart
     * @return Response
     */
    #[Route('/panier/reinitialiser/', name: 'reini_cart')]
    public function reinitialiser(Cart $cart): Response
    {
        $cart->remove();
        return $this->redirectToRoute('app_creneaux_index');
    }
}