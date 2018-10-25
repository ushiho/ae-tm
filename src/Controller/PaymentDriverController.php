<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\PaymentDriver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

    public function calculateTotalPrice(Mission $mission){
        if($mission){
            return $mission->getDriver()->getSalairePerDay() * $mission->getEndDate()->diff($mission->getStartDate())->days;
        }else{
            return null;
        }
    }

    public function calculateRemainingPrice(PaymentDriver $paymentDriver){
        if($paymentDriver){
            if($paymentDriver->getRemainingPrice() >= $paymentDriver->getPrice()){
                $paymentDriver->setRemainingPrice($paymentDriver->getRemainingPrice - $paymentDriver->getPrice());
            }else{
                dd('The price is greater than the remaining price! do you want to  continue this process?');
            }
        }
    }

    public function init(Mission $mission){
        if($mission){
            $paymentDriver = new PaymentDriver();
            $paymentDriver->setTotalPrice(PaymentDriverController::calculateTotalPrice($mission));
            $paymentDriver->setRemainingPrice(PaymentDriverController::calculateTotalPrice($mission));
            return $paymentDriver;
        }else{
            return null;
        }
    }
}
