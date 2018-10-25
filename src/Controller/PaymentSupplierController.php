<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Allocate;
use App\Entity\PaymentSupplier;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaymentSupplierController extends AbstractController
{
   
    public function init(Mission $mission){
        if($mission){
            $paymentSupplier = new PaymentSupplier();
            $paymentSupplier->setTotalPriceToPay(PaymentSupplierController::calculateTotalPrice($mission));
            $paymentSupplier->setRemainingPrice(PaymentSupplierController::calculateTotalPrice($mission));
            return $paymentSupplier;
        }else{
            return null;
        }
    }

    public function calculateTotalPrice(Mission $mission){
        if($mission && $mission->getAllocate()){
            return $mission->getAllocate()->getPricePerDay() * $mission->getAllocate()->getEndDate()->diff($mission->getAllocate()->getStartDate())->days;
        }else{
            return null;
        }
    }
}
