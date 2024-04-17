<?php
namespace App\Service;

use App\Repository\CreneauxRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permet de gérer un panier en session plutot que de tout implémenter dans le controller
 */
class Cart
{
    //proprieté privée
    private $requestStack;
    private $repository;

    public function __construct(RequestStack $requestStack, CreneauxRepository $repository)
    {
        $this->requestStack = $requestStack;
        $this->repository = $repository;
    }


    /**
     * Crée un tableau associatif id => quantité et le stocke en session
     *
     * @param int $id
     */
    public function addToCart(int $id)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
    
       if (empty($cart[$id])) {
          $cart[$id] = 1;
          } else {    
        // Retourner false pour indiquer que la redirection ne doit pas être effectuée
        return false;
          }
       //La méthode set est utilisée pour associer le tableau $cart à la clé 'cart' dans la session.
       $this->requestStack->getSession()->set('cart', $cart);

      // Retourner true pour indiquer que la redirection doit être effectuée
      return true;
    }


    /**
     * Récupère le contenu du panier en session
     *
     * @return array
     */
    
    public function get(): array
    {
        return $this->requestStack->getSession()->get('cart');
    }
   

    /**
          * Récupère le panier en session, puis récupère les objets créneaux de la bdd
          * et calcule les totaux
          *ou récupère les détails du panier stocké en session.
          * @return array
          */
    public function getDetails(): array
        {
            $cartCreneaux = ['creneaux' => [],'totals' => ['quantity' => 0,'price' => 0,],];
            // $cartCreneaux = [
            //     'creneaux' => [],
            //     'totals' => [
            //         'quantity' => 0,
            //         'price' => 0,
            //     ],
            // ];
       
            $cart = $this->requestStack->getSession()->get('cart', []);
            if ($cart) {
                // Itération sur chaque élément du panier ($cart) où $id est l'identifiant du créneau et $quantity est sa quantité
                foreach ($cart as $id => $quantity) {
                    
               // Récupération de l'objet créneau dans bdd correspondant à l'identifiant de l'article dans le panier en utilisant le CreneauxRepository
                    $currentCreneau = $this->repository->find($id);
                    
                    //si l'objet créneau est trouvé
                    if ($currentCreneau) {
                     // Ajout de l'objet créneau au tableau 'creneaux' de $cartCreneaux
                        $cartCreneaux['creneaux'][] = $currentCreneau;

                        //Ajout de la quantité de l'article ajouté au total de la quantité du panier
                        $cartCreneaux['totals']['quantity'] += $quantity;

                        //C'est le calcul du prix total de l'article ajouté au panier. Il multiplie la quantité de l'article ajouté ($quantity) par le prix unitaire de cet article ($currentCreneau->getPermis()->getPrice()).
                        
                        $cartCreneaux['totals']['price'] += $quantity * $currentCreneau->getPermis()->getPrice();
                    }
                }
            }
            return $cartCreneaux;
        }
     
     
         
    public function removeItem(int $id): void
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $cartDetails = $this->getDetails();

        foreach ($cartDetails['creneaux'] as $key => $creneau) {
            
            if ($creneau->getId() == $id) {

                unset($cartDetails['creneaux'][$key]);
                $nb = $this->requestStack->getSession()->get('nb', 0);
                if ($nb > 0) {
                    $nb--;
                    if ($nb > 0) {
                        $this->requestStack->getSession()->set('nb', $nb);
                    } else {
                        $this->requestStack->getSession()->remove('nb'); // Supprimer la variable 'nb' si elle est égale à zéro
                    }
                }

                // Mise à jour des totaux
                if (isset ($cartDetails['totals'])) {
                    $cartDetails['totals']['quantity']--;
                    $cartDetails['totals']['price'] -= $creneau->getPermis()->getPrice();
                }
                unset($cart[$creneau->getId()]);
                $this->requestStack->getSession()->set('cart', $cart);
                return; // Sortir de la boucle une fois que l'élément est supprimé
            }
        }
    }
    /**
     * Initialiser le panier en session
     *
     * @return void
     */
    public function remove(): void
    {
        $this->requestStack->getSession()->remove('cart');
        $this->requestStack->getSession()->remove('nb');
    }

    /**
     * Récupère le panier en session, puis récupère les objets creneau de la bdd
     * et calcule les totaux
     *
     * @return array
     */
   

}