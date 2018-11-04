<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Payment;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Controller\PaymentDriverController;
use App\Controller\PaymentSupplierController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\PaymentSupplier;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="allPayments")
     * @Route("/payment/mission/{idMission}", name="paymentsOfMission", requirements={"idMission"="\d+"})
     * @Route("/payment/mission/{idProject}", name="paymentsOfProject", requirements={"idProject"="\d+"})
     */
    public function show(PaymentRepository $repo, ProjectRepository $projectRepo, MissionRepository $missionRepo, $idMission=null, $idProject=null, Request $request)
    {
        $payments = [];
        if($idMission && $request->attributes->get('_route')== "paymentsOfMission"){
            $payments = $repo->findByMission($missionRepo->find($idMission));
        }else if($idProject && $request->attributes->get('_route')=="paymentsOfProject"){
            $payments = $repo->findByProject($projectRepo->find($idProject));
        }else{
            $payments = $repo->findAll();
        }
        return $this->render('payment/paymentBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'payments' => $payments,
        ]);
    }

    public function init(Mission $mission){
        if($mission){
            $payment = new Payment();
            $toPayToSupplier = PaymentSupplierController::calculateTotalPrice($mission);
            $toPayToDriver = PaymentDriverController::calculateTotalPrice($mission);
            $total = $toPayToDriver + $toPayToSupplier;
            $payment->setTotalPriceToPayToDriver($toPayToDriver)
                    ->setTotalPriceToPayToSupplier($toPayToSupplier)
                    ->setTotalPrice($total)
                    ->setRemainingPrice($total)
                    ->setTotalPricePaid(0)
                    ->setTotalPricePaidToDriver(0)
                    ->setTotalPricePaidToSupplier(0)
                    ->setRemainigPriceToSupplier($toPayToSupplier)
                    ->setRemainingPriceToDriver($toPayToDriver)
                    ->setMission($mission);
            return $payment;
        }else{
            return null;
        }
    }

    /**
     * @Route("/payment/{id}/detail", name="paymentDetail", requirements={"id"="\d+"})
     */
    public function detail($payment=null){
        if ($payment) {
            return $this->render('payment/show.html.twig', [
                'connectedUser' => $this->getUser(),
                'payment' => $payment,
            ]);
        } else {
            $request->getSession()->getFlashBag()->add('paymentError', "There is No selected Payment to show it's details!");
            return $this->redirectToRoute('allPayments');
        }
    }

    /**
     * @Route("/payment/{id}/show", name="showPayment", requirements={"id"="\d+"})
     */
    public function edit($id=null, ObjectManager $manager, Request $request, PaymentRepository $repo){
        $payment = $repo->find($id);
        if ($payment) {
            return $this->render('payment/show.html.twig', [
                'payment' => $payment,
                'connectedUser' => $this->getUser(),
            ]);
        } else {
            $request->getSession()->getFlashBag()->add('paymentError', "Please select a payment to show more details!");
            return $this->redirectToRoute('allPayments');
        }
        
    }

    public function addPaymentSupplier(PaymentSupplier $paymentSupplier, Payment $payment){
        if($paymentSupplier && $payment){
            $payment->setTotalPricePaid($payment->getTotalPricePaid() + $paymentSupplier->getPrice())
                    ->setRemainingPrice($payment->getRemainingPrice() - $paymentSupplier->getPrice())
                    ->setTotalPricePaidToSupplier($payment->getTotalPricePaidToSupplier() + $paymentSupplier->getPrice())
                    ->setRemainigPriceToSupplier($payment->getRemainigPriceToSupplier() - $paymentSupplier->getPrice())
                    ->setFinished($payment->getRemainingPrice()==0);
            return $payment;
        }else{
            return null;
        }
    }

    // public function addPaymentDriver(PaymentSupplier $paymentSupplier, Payment $payment)
    // {
    //     if ($paymentSupplier && $payment) {
    //         $payment->addPaymentSupplier($paymentSupplier);
    //         $payment->setTotalPricePaid($payment->getTotalPricePaid() + $paymentSupplier->getPrice())
    //             ->setRemainingPrice($payment->getRemainingPrice() - $paymentSupplier->getPrice())
    //             ->setFinished($payment->getRemainingPrice() == 0);
    //     } else {
    //         return null;
    //     }
    // }

}
