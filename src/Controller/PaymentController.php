<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="allPayments")
     */
    public function show()
    {
        return $this->render('payment/paymentBase.html.twig', [
            'connectedUser' => $this->getUser(),
        ]);
    }
}