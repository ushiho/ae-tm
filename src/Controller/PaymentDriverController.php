<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\PaymentDriver;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Repository\PaymentDriverRepository;
use Symfony\Component\HttpFoundation\Request;
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
            $paymentDriver = $request->findByPayment($paymentRepo->find($idPayment));
        } else {
            $paymentDriver = $repo->findAll();
        }
        return $this->render('payment_driver/base.html.twig', [
            'connectedUser' => $this->getUser(),
            'paymentDriver' => $paymentDriver,
        ]);
    }
}
