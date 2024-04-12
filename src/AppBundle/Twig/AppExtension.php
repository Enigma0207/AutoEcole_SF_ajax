<?php
// src/AppBundle/Twig/AppExtension.php
namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private RequestStack $requestStack,
    ) {
        // Accessing the session in the constructor is *NOT* recommended, since
        // it might not be accessible yet or lead to unwanted side-effects
        // $this->session = $requestStack->getSession();
    }
    

    public function getFunctions() : array
    {
        return [
            new TwigFunction('array_key_existe', [$this, 'arrayKeyExiste']),
        ];
    }

    public function arrayKeyExiste($key)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);
        $result = array_key_exists($key, $cart);

        return $result;
    }
}