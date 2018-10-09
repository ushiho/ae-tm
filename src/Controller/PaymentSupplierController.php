<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PaymentSupplierController extends AbstractController
{
    /**
     * @Route("/payment/supplier", name="payment_supplier")
     */
    public function index()
    {
        return $this->render('payment_supplier/index.html.twig', [
            'controller_name' => 'PaymentSupplierController',
        ]);
    }
}
