<?php

namespace App\Controller;

use App\Entity\Driver;
use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\Project;
use App\Entity\PaymentDriver;
use App\Form\PaymentDriverType;
use App\Form\PaymentExportType;
use App\Repository\DriverRepository;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Repository\PaymentDriverRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class PaymentDriverController extends AbstractController
{

    public function calculateTotalPrice(Mission $mission)
    {
        if ($mission) {
            $days = $mission->getEndDate()->diff($mission->getStartDate())->days + 1;
            $amount = $mission->getSalaire() * $days;
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
            $paymentDriver->setTotalPrice($data['amount'])
                        ->setDaysToPay($data['days'])
                        ->setPrice($data['amount'] * $data['days']);


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
        if($this->getUser()->getRole() != 3){
            
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
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/paymentDriver/add/{idPayment}", name="addPaymentDriver")
     * @Route("/payment/{idPayment}/paymentDriver/{id}/edit", name="editPaymentDriver", requirements={"id"= "\d+"})
     */
    public function action(PaymentDriver $paymentDriver = null, ObjectManager $manager, Request $request, DriverRepository $driverRepo, PaymentDriverRepository $repo, $idPayment = null, PaymentRepository $paymentRepo)
    {
        if($this->getUser()->getRole() != 3){

            $payment = $paymentRepo->find($idPayment);
            if (!$paymentDriver) {
                $paymentDriver = new PaymentDriver();
            }
            if ($payment) {
                $form = $this->createForm(PaymentDriverType::class, $paymentDriver);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
    
                    $paymentDriver = $this->completeDatas($payment, $paymentDriver);
                    if ($this->compareDays($paymentDriver, $request, $repo)) {
    
                        $this->save($manager, $paymentDriver, $request, $repo);
    
                        return $this->redirectToRoute('paymentDriverByPayment', [
                            'idPayment' => $payment->getId(),
                        ]);
                    } else {
                        $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'Number of days paid to that driver is more than his expanses!');
                    }
                }
    
                return $this->render('payment/paymentDriverForm.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'form' => $form->createView(),
                    'paymentDriver' => $paymentDriver,
                ]);
            } else {
                $request->getSession()->getFlashBag()->add('paymentMsg', 'Please select a payment from the list below to link the new payment!');
    
                return $this->redirectToRoute('allPayments');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }


    public function save(ObjectManager $manager, PaymentDriver $paymentDriver, Request $request, PaymentDriverRepository $repo)
    {
        $payment = PaymentController::addPaymentDriver($paymentDriver, $repo);
        $manager->persist($manager->merge($payment));
        $manager->persist($paymentDriver);
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
        if($this->getUser()->getRole() != 3){

            if ($paymentDriver && $paymentDriver->getId()) {
                return $this->render('payment/paymentDriverShow.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'paymentDriver' => $paymentDriver,
                ]);
            }
            $request->getSession()->getFlashBag()->add('paymentDriverMsg', 'Please select a payment from the list below to show details!');
    
            return $this->redirectToRoute('allPaymentsDriver');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/paylentDriver/{id}/delete", name="deletePaymentDriver", requirements={"id" = "\d+"})
     */
    public function delete(PaymentDriver $paymentDriver = null, ObjectManager $manager, Request $request)
    {
        if($this->getUser()->getRole() != 3){

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
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function addMoneyToPayment(PaymentDriver $paymentDriver)
    {
        if ($paymentDriver && $paymentDriver->getId()) {
            $payment = $paymentDriver->getPayment();
            
            return $paymentDriver->getPayment()
                    ->setRemainingPrice($payment->getRemainingPrice() + $paymentDriver->getPrice())
                    ->setRemainingPriceToDriver($payment->getRemainingPriceToDriver() + $paymentDriver->getPrice())
                    ->setTotalPricePaid($payment->getTotalPricePaid() - $paymentDriver->getPrice())
                    ->setTotalPricePaidToDriver($payment->getTotalPricePaidToDriver() - $paymentDriver->getPrice())
                    ->setTotalDaysPaid($payment->getTotalDaysPaid() - $paymentDriver->getDaysPaid())
                    ->setRemainingDays($payment->getRemainingDays() + $paymentDriver->getDaysPaid())
                    ->removePaymentDriver($paymentDriver);

        }
    }

    public function compareDays(PaymentDriver $paymentDriver, Request $request, PaymentDriverRepository $paymentDriverRepo)
    {
        if ($paymentDriver && $paymentDriver->getId() && $request->attributes->get('_route') == 'editPaymentDriver') {
            //$cond = $paymentDriver->getPrice() <= $price + $payment->getRemainingPriceToDriver();
            $cond = $paymentDriver->getDaysPaid() <= $paymentDriverRepo->find($paymentDriver->getId())->getDaysPaid() + $paymentDriver->getPayment()->getRemainingDays();
        } else {
            $cond = $paymentDriver->getDaysPaid() <= $paymentDriver->getPayment()->getRemainingDays();
        }

        return $cond;
    }

    public function completeDatas(Payment $payment, PaymentDriver $paymentDriver)
    {
        $mission = $payment->getMission();
        $pricePaid = $mission->getSalaire() * $paymentDriver->getDaysPaid();

        return $paymentDriver->setTotalPrice($payment->getTotalPriceToPayToDriver())
                    ->setDaysToPay($payment->getTotalDaysToPay())
                    ->setDriver($mission->getDriver())
                    ->setPayment($payment)
                    ->setPrice($pricePaid);
    }

    public function periodOfWork($period){
        switch ($period) {
            case '1':
                return 1;
            case '2':
                return 7;
            case '3':
                return 30;
            default:
                return 0;
        }
    }

    /**
     * @Route("/driver/payment/export", name="exportPaymentDriver")
     */
    public function exportPayment(Request $request, MissionRepository $missionRepo, PaymentDriverRepository $paymentDriverRepo)
    {
        if($this->getUser()->getRole() != 3){
            $form = $this->createForm(PaymentExportType::class);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();
                $managedData = $this->manageData($missionRepo->findByDriverAndProject($data['project'], $data['driver']), $paymentDriverRepo);
                return $this->print($data['project'], $data['driver'], $managedData);
            }
            return $this->render('driver/exportPayment.html.twig', [
                'form' => $form->createView(),
                'connectedUser' => $this->getUser(),
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function manageData($missions, PaymentDriverRepository $paymentDriverRepo)
    {
        $managedData = [];
        foreach ($missions as $mission) {
            $key = "FROM ".$mission->getStartDate()->format('M d/m/y')." TO ".$mission->getEndDate()->format('M d/m/y')." [ ".$mission->getAllocate()->getVehicle()->getMatricule()." ".$mission->getAllocate()->getVehicle()->getType()->getName()." ]";
            $managedData[$key] = $paymentDriverRepo->findByPayment($mission->getPayment());
        }
        return $managedData;
    }

    public function print(Project $project, Driver $driver, array $data)
    {
        if($this->getUser()->getRole()!=3 && $project){
            $fileName = (new \DateTime())->format('Hidmy');
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            
            // Instantiate Dompdf with our options
            $dompdf = new Dompdf($pdfOptions);
            
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('exportedFile/payment_driver.html.twig', [
                'project' => $project,
                'driver' => $driver,
                'data' => $data,
            ]);
            
            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
            // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
            $dompdf->setPaper('A4', 'portrait');
    
            // Render the HTML as PDF
            $dompdf->render();
    
            // Output the generated PDF to Browser (force download)
            $dompdf->stream($fileName.".pdf", [
                "Attachment" => false,
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }
}
