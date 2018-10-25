<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Payment;
use App\Controller\PaymentDriverController;
use App\Controller\PaymentSupplierController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    public function init(Mission $mission){
        if($mission){
            $payment = new Payment();
            $total = PaymentDriverController::calculateTotalPrice($mission) + PaymentSupplierController::calculateTotalPrice($mission);
            $payment->setTotalPriceToPayToDriver(PaymentDriverController::calculateTotalPrice($mission))
                    ->setTotalPriceToPayToSupplier(PaymentSupplierController::calculateTotalPrice($mission))
                    ->setTotalPrice($total)
                    ->setRemainingPrice($total)
                    ->setTotalPricePaid(0)
                    ->setMission($mission);
            return $payment;
        }else{
            return null;
        }
    }

}
