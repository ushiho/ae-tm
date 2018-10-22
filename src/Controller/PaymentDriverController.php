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

    public function calculateTotalPrice(Driver $driver, Mission $mission){
        if($driver && $mission){
            return $driver->getSalairePerDay() * ($mission->getEndDate() - $mission->getStartDate());
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
}
