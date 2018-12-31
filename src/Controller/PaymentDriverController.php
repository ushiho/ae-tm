<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\PaymentDriver;
use App\Form\PaymentDriverType;
use App\Repository\DriverRepository;
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

    public function calculateTotalPrice(Mission $mission)
    {
        if ($mission) {
            $days = $mission->getEndDate()->diff($mission->getStartDate())->days + 1;
            $amount = $mission->getDriver()->getSalairePerDay() * $days;
            return ['days' => $days, 'amount' => $amount];
        } else {
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

    public function init(Mission $mission)
    {
        if ($mission) {
            $paymentDriver = new PaymentDriver();
            $data = PaymentDriverController::calculateTotalPrice($mission);
            $paymentDriver->setTotalPrice($data['amount']);
            $paymentDriver->setDaysToPay($data['days']);

            return $paymentDriver;
        } else {
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
        if ($idMission && $request->attributes->get('_route') == 'paymentDriverByMission') {
            $paymentDriver = $repo->findByMission($missionRepo->find($idMission));
        } elseif ($idProject && $request->attributes->get('_route') == 'paymentDriverByProject') {
            $paymentDriver = $repo->findByProject($projectRepo->find($idProject));
        } elseif ($idPayment && $request->attributes->get('_route') == 'paymentDriverByPayment') {
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
     * @Route("/payment/paymentDriver/add/{idPayment}", name="addPaymentDriver")
     * @Route("/payment/{idPayment}/paymentDriver/{id}/edit", name="editPaymentDriver", requirements={"id"= "\d+"})
     */
    public function action(PaymentDriver $paymentDriver = null, ObjectManager $manager, Request $request, DriverRepository $driverRepo, PaymentDriverRepository $repo, $idPayment = null, PaymentRepository $paymentRepo)
    {
        // $payment = $paymentRepo->find($idPayment);
        $payment = $request->getSession()->get('payment');
        if (!$paymentDriver) {
            $paymentDriver = new PaymentDriver();
        }
        if ($payment || $paymentDriver) {
            $params = $this->testParams($payment, $paymentDriver, $request);
            $form = $this->createForm(PaymentDriverType::class, $params['paymentDriver']);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if ($this->compareDays($params['paymentDriver'], $request, $params['payment'], $params['days'])) {
                    $driver = $driverRepo->findByMission($payment->getMission());
                    $paymentDriver = $this->completeDatas($params['paymentDriver'], $params['payment'], $driver);
                    $paymentDriver->setPrice(($payment->getMission()->getSalaire()/$payment->getMission()->getPeriodOfWork()) * $params['days']);
                    $this->save($manager, $paymentDriver, $params['payment'], $request, $params);

                    return $this->redirectToRoute('paymentDriverByPayment', [
                        'idPayment' => $params['payment']->getId(),
                    ]);
                } else {
                    $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'Number of days paid to that driver is more than his expanses!');
                }
            }

            return $this->render('payment/paymentDriverForm.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
                'paymentDriver' => $params['paymentDriver'],
            ]);
        } else {
            $request->getSession()->getFlashBag()->add('paymentMsg', 'Please select a payment from the list below to link the new payment!');

            return $this->redirectToRoute('allPayments');
        }
    }

    public function completeDatas(PaymentDriver $paymentDriver, Payment $payment, $driver)
    {
        if ($paymentDriver && !$paymentDriver->getId()) {
            $paymentDriver->setDriver($driver)
                        ->setPayment($payment)
                        ->setTotalPrice($payment->getTotalPriceToPayToDriver())
                        ->setDaysToPay($payment->getTotalDaysToPay());
        }

        return $paymentDriver;
    }

    public function save(ObjectManager $manager, PaymentDriver $paymentDriver, Payment $payment, Request $request, $params)
    {
        $payment = PaymentController::addPaymentDriver($paymentDriver, $payment, $params['price'], $params['days']);
        $manager->persist($manager->merge($payment));
        $manager->persist($manager->merge($paymentDriver));
        $manager->flush();
        $request->getSession()->clear();
        $request->getSession()->getFlashBag()->add('paymentDriverMsg', $this->messageOfAction($paymentDriver));
    }

    public function messageOfAction(PaymentDriver $paymentDriver)
    {
        if ($paymentDriver && $paymentDriver->getId()) {
            return 'The payment driver was successfully modified ';
        }

        return 'The payment driver was successfully added';
    }

    /**
     * @Route("/payment/paymentDriver/{id}/show", name="showPaymentDriver")
     */
    public function showDetails(PaymentDriver $paymentDriver = null, Request $request)
    {
        if ($paymentDriver && $paymentDriver->getId()) {
            return $this->render('payment/paymentDriverShow.html.twig', [
                'connectedUser' => $this->getUser(),
                'paymentDriver' => $paymentDriver,
            ]);
        }
        $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'Please select a payment from the list below to show details!');

        return $this->redirectToRoute('allPaymentsDriver');
    }

    /**
     * @Route("/payment/paylentDriver/{id}/delete", name="deletePaymentDriver", requirements={"id" = "\d+"})
     */
    public function delete(PaymentDriver $paymentDriver = null, ObjectManager $manager, Request $request)
    {
        if ($paymentDriver && $paymentDriver->getId()) {
            $payment = $this->addMoneyToPayment($paymentDriver);
            $manager->persist($manager->merge($payment));
            $manager->remove($paymentDriver);
            $manager->flush();
            $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'The payment was successfully  deleted!');
            $request->getSession()->clear();
        } else {
            $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'There is no selected payment to delete!');
        }

        return $this->redirectToRoute('allPaymentsDriver');
    }

    public function addMoneyToPayment(PaymentDriver $paymentDriver)
    {
        if ($paymentDriver && $paymentDriver->getId()) {
            $payment = $paymentDriver->getPayment();
            $payment->setRemainingPrice($payment->getRemainingPrice() + $paymentDriver->getPrice())
                    ->setRemainingPriceToDriver($payment->getRemainingPriceToDriver() + $paymentDriver->getPrice())
                    ->setTotalPricePaid($payment->getTotalPricePaid() - $paymentDriver->getPrice())
                    ->setTotalPricePaidToDriver($payment->getTotalPricePaidToDriver() - $paymentDriver->getPrice())
                    ->setTotalDaysPaid($payment->getTotalDaysPaid() - $paymentDriver->getDaysPaid())
                    ->setRemainingDays($payment->getRemainingDays() + $paymentDriver->getDaysPaid())
                    ->removePaymentDriver($paymentDriver);

            return $paymentDriver;
        }
    }

    public function compareDays(PaymentDriver $paymentDriver, Request $request, Payment $payment, $days)
    {
        if ($paymentDriver && $paymentDriver->getId() && $request->attributes->get('_route') == 'editPaymentDriver') {
            //$cond = $paymentDriver->getPrice() <= $price + $payment->getRemainingPriceToDriver();
            $cond = $paymentDriver->getDaysPaid() <= $days + $payment->getRemainingDays();
        } else {
            $cond = $paymentDriver->getDaysPaid() <= $payment->getRemainingDays();
        }

        return $cond;
    }

    public function testParams(Payment $payment, PaymentDriver $paymentDriver = null, Request $request)
    {
        if ($paymentDriver && $paymentDriver->getId() && $request->attributes->get('_route') == 'editPaymentDriver') {
            $days = $paymentDriver->getDaysPaid();
            $price = $paymentDriver->getDriver()->getSalaire() * $days;
            $payment = $paymentDriver->getPayment();
        } else {
            $paymentDriver = new PaymentDriver();
            $price = 0;
            $days = 0;
        }

        return ['price' => $price,
        'days' => $days,
        'payment' => $payment,
        'paymentDriver' => $paymentDriver,
        ];
    }
}
