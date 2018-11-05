<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Allocate;
use App\Entity\PaymentSupplier;
use App\Form\PaymentSupplierType;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Repository\PaymentSupplierRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use App\Entity\Payment;
use App\Repository\AllocateRepository;
use App\Repository\SupplierRepository;

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

    /**
     * @Route("/payment/paymentSupplier/show", name="allPaymentsSupplier")
     * @Route("/payment/paymentSupplier/mission/{idMission}", name="paymentSupplierByMission", requirements={"idMission"="\d+"})
     * @Route("/payment/paymentSupplier/project/{idProject}", name="paymentSipplierByProject", requirements={"idProject"="\d+"})
     * @Route("/payment/paymentSupplier/payment/{idPayment}", name="paymentSupplierByPayment", requirements={"idPayment"="\d+"})
     */
    public function show(Request $request, PaymentSupplierRepository $repo, ProjectRepository $projectRepo, MissionRepository $missionRepo, $idMission=null, $idProject=null, PaymentRepository $paymentRepo, $idPayment=null){
        $paymentSupplier = [];
        if($idMission && $request->attributes->get('_route') == "paymentSupplierByMission"){
            $paymentSupplier = $repo->findByMission($missionRepo->find($idMission));
        }else if($idProject && $request->attributes->get('_route') == "paymentSipplierByProject"){
            $paymentSupplier = $repo->findByProject($projectRepo->find($idProject));
        }else if($idPayment && $request->attributes->get('_route') == "paymentSupplierByPayment"){
            $paymentSupplier = $repo->findByPayment($paymentRepo->find($idPayment));
        }else{
            $paymentSupplier = $repo->findAll();
        }
        
        return $this->render('payment/paymentSupplierBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'paymentSupplier' => $paymentSupplier,
        ]);
    }

    /**
     * @Route("/payment/paymentSupplier/add", name="addPaymentSupplier")
     * @Route("/payment/paymentSupplier/{id}/edit", name="editPaymentSupplier", requirements={"id"="\d+"})
     */
    public function action($id=null, PaymentSupplierRepository $repo, ObjectManager $manager, Request $request, AllocateRepository $rentRepo, SupplierRepository $supplierRepo){
        $payment = $request->getSession()->get('payment');// !=null => create a payment
        $paymentSupplier = $repo->find($id); // !=null modify/ update
        if($payment || $paymentSupplier){
                if($payment && $payment->getRemainigPriceToSupplier()==0){
                    $request->getSession()->getFlashBag()->add('paymentError', "All supplier expenses are paid, you can not add one! ");
                }else if($paymentSupplier==null){
                    $paymentSupplier = new PaymentSupplier();
                }else{
                    $payment = $paymentSupplier->getPayment();
                }
                $form = $this->createForm(PaymentSupplierType::class, $paymentSupplier);
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                    if($paymentSupplier->getPrice() <= $payment->getTotalPriceToPayToSupplier()){
                        $paymentSupplier = $this->completeDatas($paymentSupplier, $payment, $rentRepo, $supplierRepo);                        
                        $this->save($manager, $paymentSupplier, $payment, $request, $rentRepo);
                        return $this->redirectToRoute("paymentSupplierByPayment", [
                            'idPayment' => $payment->getId(),
                        ]);
                    }else{
                        $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "The price given to that supplier is more than his expenses (".$payment->getTotalPriceToPayToSupplier()." DH)");
                    }
                }
                return $this->render('payment/paymentSupplierForm.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'form' => $form->createView(),
                    'paymentSupplier' => $paymentSupplier,
                ]);
        }else{
            $request->getSession()->getFlashBag()->add('paymentMsg', "Please select a payment from the list below to link the new payment!");
            return $this->redirectToRoute('allPayments');
        }
    }

    public function completeDatas(PaymentSupplier $paymentSupplier, Payment $payment, AllocateRepository $rentRepo, SupplierRepository $supplierRepo){
        if($paymentSupplier){
            $rent = $rentRepo->find($payment->getMission()->getAllocate()->getId());
            $paymentSupplier->setAllocate($rent)
                            ->setSupplier($supplierRepo->findByMission($payment->getMission()))
                            ->setTotalPriceToPay($payment->getTotalPriceToPayToSupplier())
                            ->setTotalPricePaid($payment->getTotalPricePaidToSupplier() + $paymentSupplier->getPrice())
                            ->setRemainingPrice($payment->getRemainigPriceToSupplier() - $paymentSupplier->getPrice())
                            ->setPayment($payment);
            return $paymentSupplier;
        }else{
            return null;
        }
    }

    /**
     * @Route("/payment/paymentSupplier/{id}/show", name="showPaymentSupplier")
     */
    public function showDetails(PaymentSupplier $paymentSupplier=null, Request $request){
        if($paymentSupplier){
            return $this->render('payment/paymentSupplierShow.html.twig', [
                'connectedUser' => $this->getUser(),
                'paymentSupplier' => $paymentSupplier,
            ]);
        }else{
            $request->getSession()->getFlashBag()->add('paymentSupplierError', "Please select a payment from the lise below to show details!");
            return $this->redirectToRoute("allPaymentsSupplier");
        }
    }

    /**
     * @Route("/payment/paymentSupplier/{id}/delete", name="deletePaymentSupplier")
     */
    public function delete(PaymentSupplier $paymentSupplier=null, ObjectManager $manager, Request $request){
        if($paymentSupplier){
            $payment = $this->addMoneyToPayment($paymentSupplier);
            $manager->remove($paymentSupplier);
            $manager->persist($manager->merge($payment));
            $manager->flush();
            $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "The payment successfully deleted!");
        }else{
            $request->getSession()->getFlashBag()->add('paymentSupplierError', "There is no selected payment to delete!");
        }
        return $this->redirectToRoute('allPaymentsSupplier');
    }

    public function addMoneyToPayment(PaymentSupplier $paymentSupplier){
        if($paymentSupplier && $paymentSupplier->getPayment()){
            $payment = $paymentSupplier->getPayment();
            $payment->setRemainingPrice($payment->getRemainingPrice()+$paymentSupplier->getPrice())
                    ->setTotalPricePaid($payment->getTotalPricePaid()-$paymentSupplier->getPrice())
                    ->removePaymentSupplier($paymentSupplier);
            return $payment;
        }
    }

    public function messageOfAction(PaymentSupplier $paymentSupplier){
        if($paymentSupplier && $paymentSupplier->getId()){
            return "The payment supplier was successfully modified ";
        }else{
            return "The payment supplier was successfully added";
        }
    }

    public function save(ObjectManager $manager, PaymentSupplier $paymentSupplier, Payment $payment, Request $request){
        $payment = PaymentController::addPaymentSupplier($paymentSupplier, $payment);
        $payment->addPaymentSupplier($paymentSupplier);
        $manager->persist($manager->merge($payment));
        $manager->persist($manager->merge($paymentSupplier));
        $manager->flush();
        $request->getSession()->clear();
        $request->getSession()->getFlashBag()->add('paymentSupplierMsg', $this->messageOfAction($paymentSupplier));
    }
}
