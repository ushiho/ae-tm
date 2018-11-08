<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\PaymentDriver;
use App\Form\PaymentDriverType;
use App\Repository\DriverRepository;
use App\Controller\PaymentController;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Repository\PaymentDriverRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
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
            return $mission->getDriver()->getSalairePerDay() * ($mission->getEndDate()->diff($mission->getStartDate())->days+1);
        }else{
            return null;
        }
    }

    // public function calculateRemainingPrice(PaymentDriver $paymentDriver, Request $request){
    //     if($paymentDriver){
    //         if($paymentDriver->getRemainingPrice() >= $paymentDriver->getPrice()){
    //             $paymentDriver->setRemainingPrice($paymentDriver->getRemainingPrice - $paymentDriver->getPrice());
    //         }else{
    //             'The price is greater than the remaining price! do you want to  continue this process?'
    //         }
    //     }
    // }

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

    /**
     * @Route("/payment/paymentDriver/show", name="allPaymentsDriver")
     * @Route("/payment/paymentDriver/mission/{idMission}", name="paymentDriverByMission", requirements={"idMission"="\d+"})
     * @Route("/payment/paymentDriver/project/{idProject}", name="paymentDriverByProject", requirements={"idProject"="\d+"})
     * @Route("/payment/paymentDriver/payment/{idPayment}", name="paymentDriverByPayment", requirements={"idPayment"="\d+"})
     */
    public function show(Request $request, PaymentDriverRepository $repo, ProjectRepository $projectRepo, MissionRepository $missionRepo, $idMission = null, $idProject = null, PaymentRepository $paymentRepo, $idPayment = null)
    {
        $paymentDriver = [];
        if ($idMission && $request->attributes->get('_route') == "paymentDriverByMission") {
            $paymentDriver = $repo->findByMission($missionRepo->find($idMission));
        } else if ($idProject && $request->attributes->get('_route') == "paymentDriverByProject") {
            $paymentDriver = $repo->findByProject($projectRepo->find($idProject));
        } else if ($idPayment && $request->attributes->get('_route') == "paymentDriverByPayment") {
            $paymentDriver = $repo->findByPayment($paymentRepo->find($idPayment));
        } else {
            $paymentDriver = $repo->findAll();
        }
        return $this->render('payment/paymentDriverBase.html.twig', [
            'connectedUser' => $this->getUser(),
            'paymentDriver' => $paymentDriver,
        ]);
    }

    /**
     * @Route("/payment/paymentDriver/add", name="addPaymentDriver")
     * @Route("/payment/paymentDriver/{id}/edit", name="editPaymentDriver", requirements={"id"= "\d+"})
     */
    public function action(PaymentDriver $paymentDriver=null, ObjectManager $manager, Request $request, DriverRepository $driverRepo, PaymentDriverRepository $repo){
        $payment = $request->getSession()->get('payment');
        $paymentDriverDB = $request->getSession()->get('paymentDriver');
        if($payment || $paymentDriver){
            if($payment && $payment->getRemainingPriceToDriver()==0){
                $request->getSession()->getFlashBag()->add('paymentDriverMsg', "All Driver's expenses are paid, you can not add a payment!");
            }else if($paymentDriver==null){
                $paymentDriver = new PaymentDriver();
            }else{
                $payment = $paymentDriver->getPayment();
            }
            $form = $this->createForm(PaymentDriverType::class, $paymentDriver);
            $form->handleRequest($request);
            if($form->isSubmitted()&&$form->isValid()){
                if($this->comparePrice($paymentDriver, $request, $payment, $paymentDriverDB)){
                    $paymentDriver = $this->completeDatas($paymentDriver, $payment, $driverRepo, $paymentDriverDB);
                    $this->save($manager, $paymentDriver, $payment, $request, $paymentDriverDB);
                    return $this->redirectToRoute("paymentDriverByPayment", [
                        'idPayment' => $payment->getId(),
                    ]);
                }else{
                    $request->getSession()->getFlashBag()->add('paymentDriverMsg', "The Price given to that driver is more than his remaining expanses (".$payment->getRemainingPriceToDriver().") DH");
                }
            }
            return $this->render('payment/paymentDriverForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
                'paymentDriver' => $paymentDriver,
            ]);
        }else{
            $request->getSession()->getFlashBag()->add('paymentMsg', "Please select a payment from the list below to link the new payment!");
            return $this->redirectToRoute('allPayments');
        }
    }

    public function completeDatas(PaymentDriver $paymentDriver, Payment $payment, DriverRepository $driverRepo, PaymentDriver $paymentDriverDB=null){
        if($paymentDriver){
            $price = $paymentDriver->getPrice();
            if($paymentDriver->getId()&&$paymentDriverDB){
                $price -= $paymentDriverDB->getPrice();
            }else{
                $paymentDriver->setDriver($driverRepo->findByMission($payment->getMission()))
                            ->setPayment($payment);
            }
            $paymentDriver->setTotalPrice($payment->getTotalPriceToPayToDriver())
                        ->setPricePaid($payment->getTotalPricePaidToDriver() + $price)
                        ->setRemainingPrice($payment->getRemainingPriceToDriver() - $price);
        return $paymentDriver;
        }
    }

    public function save(ObjectManager $manager, PaymentDriver $paymentDriver, Payment $payment, Request $request, PaymentDriver $paymentDriverDB=null){
        $payment = PaymentController::addPaymentDriver($paymentDriver, $payment, $paymentDriverDB);
        $manager->persist($manager->merge($payment));
        $manager->persist($manager->merge($paymentDriver));
        $manager->flush();
        $request->getSession()->clear();
        $request->getSession()->getFlashBag()->add('paymentDriverMsg', $this->messageOfAction($paymentDriver));
    }

    public function messageOfAction(PaymentDriver $paymentDriver){
        if($paymentDriver && $paymentDriver->getId()){
            return "The payment driver was successfully modified ";
        }
        return "The payment driver was successfully added";
    }

    /**
     * @Route("/payment/paymentDriver/{id}/show", name="showPaymentDriver")
     */
    public function showDetails(PaymentDriver $paymentDriver=null, Request $request){
        if($paymentDriver && $paymentDriver->getId()){
            return $this->render('payment/paymentDriverShow.html.twig', [
                'connectedUser' => $this->getUser(),
                'paymentDriver' => $paymentDriver,
            ]);
        }
        $request->getSession()->getFlashBag()->add('paymentDriverMsg', "Please select a payment from the list below to show details!");
        return $this->redirectToRoute('allPaymentsDriver');

    }

    /**
     * @Route("/payment/paylentDriver/{id}/delete", name="deletePaymentDriver", requirements={"id" = "\d+"})
     */
    public function delete(PaymentDriver $paymentDriver=null, ObjectManager $manager, Request $request){
        if($paymentDriver && $paymentDriver->getId()){
            $payment = $this->addMoneyToPayment($paymentDriver);
            $manager->persist($manager->merge($payment));
            $manager->remove($paymentDriver);
            $manager->flush();
            $request->getSession()->clear();
            $request->getSession()->getFlashBag()->add('paymentDriverMsg', "The payment was successfully  deleted!");
        }else{
            $request->getSession()->getFlashBag()->add('paymentDriverMsg', "There is no selected payment to delete!");
        }
        return $this->redirectToRoute('allPaymentsDriver');
    }

    public function addMoneyToPayment(PaymentDriver $paymentDriver){
        if($paymentDriver && $paymentDriver->getId()){
            $payment = $paymentDriver->getPayment();
            $payment->setRemainingPrice($payment->getRemainingPrice()+$paymentDriver->getPrice())
                    ->setRemainingPriceToDriver($payment->getRemainingPriceToDriver()+ $paymentDriver->getPrice())
                    ->setTotalPricePaid($payment->getTotalPricePaid()-$paymentDriver->getPrice())
                    ->setTotalPricePaidToDriver($payment->getTotalPricePaidToDriver()-$paymentDriver->getPrice())
                    ->removePaymentDriver($paymentDriver);
            return $paymentDriver;
        }
    }

    public function comparePrice(PaymentDriver $paymentDriver, Request $request, Payment $payment, PaymentDriver $paymentDriverDB=null){
        if ($paymentDriver && $paymentDriverDB && $request->attributes->get('_route') == "editPaymentDriver") {
            $cond = $paymentDriver->getPrice() <= $paymentDriverDB->getPrice() + $payment->getRemainingPriceToDriver();
        } else {
            $cond = $paymentDriver->getPrice() <= $payment->getRemainingPriceToDriver();
        }
        return $cond;
    }
}
