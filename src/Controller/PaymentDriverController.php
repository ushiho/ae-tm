<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PaymentDriverController extends AbstractController
{
    /**
     * @Route("/payment/driver", name="payment_driver")
     */
    public function index()
    {
        return $this->render('payment_driver/index.html.twig', [
            'controller_name' => 'PaymentDriverController',
        ]);
    }
}
