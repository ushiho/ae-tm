<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Payment;
use App\Entity\Project;
use App\Form\ExportPaymentType;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Repository\PaymentDriverRepository;
use App\Repository\PaymentSupplierRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\PaymentSupplier;
use App\Entity\PaymentDriver;

// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="allPayments")
     * @Route("/payment/mission/{idMission}", name="paymentsOfMission", requirements={"idMission"="\d+"})
     * @Route("/payment/mission/{idProject}", name="paymentsOfProject", requirements={"idProject"="\d+"})
     */
    public function show(PaymentRepository $repo, ProjectRepository $projectRepo, MissionRepository $missionRepo, $idMission = null, $idProject = null, Request $request)
    {
        if($this->getUser()->getRole() != 3){

            $payments = [];
            if ($idMission && $request->attributes->get('_route') == 'paymentsOfMission') {
                $payments = $repo->findByMission($missionRepo->find($idMission));
            } elseif ($idProject && $request->attributes->get('_route') == 'paymentsOfProject') {
                $payments = $repo->findByProject($projectRepo->find($idProject));
            } else {
                $payments = $repo->findAll();
            }
    
            return $this->render('payment/paymentBase.html.twig', [
                'connectedUser' => $this->getUser(),
                'payments' => $payments,
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function init(Mission $mission)
    {
        if ($mission) {
            $payment = new Payment();
            $data = PaymentDriverController::calculateTotalPrice($mission);
            $toPayToSupplier = PaymentSupplierController::calculateTotalPrice($mission);
            $toPayToDriver = $data['amount'];
            $total = $toPayToDriver + $toPayToSupplier;
            $days = $data['days'];
            $payment->setTotalPriceToPayToDriver($toPayToDriver)
                    ->setTotalPriceToPayToSupplier($toPayToSupplier)
                    ->setTotalPrice($total)
                    ->setRemainingPrice($total)
                    ->setTotalPricePaid(0)
                    ->setTotalPricePaidToDriver(0)
                    ->setTotalPricePaidToSupplier(0)
                    ->setRemainigPriceToSupplier($toPayToSupplier)
                    ->setRemainingPriceToDriver($toPayToDriver)
                    ->setTotalDaysToPay($days)
                    ->setTotalDaysPaid(0)
                    ->setRemainingDays($days)
                    ->setMission($mission)
                    ->setFinished(false);

            return $payment;
        } else {
            return null;
        }
    }

    public function daysBetween(String $dt1, String $dt2)
    {
        return date_diff(
            date_create($dt2),
            date_create($dt1)
        )->format('%a');
    }

    /**
     * @Route("/payment/{id}/detail", name="paymentDetail", requirements={"id"="\d+"})
     */
    public function detail($payment = null)
    {
        if($this->getUser()->getRole() != 3){

            if ($payment) {
                return $this->render('payment/show.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'payment' => $payment,
                ]);
            } else {
                $request->getSession()->getFlashBag()->add('paymentError', "There is No selected Payment to show it's details!");
    
                return $this->redirectToRoute('allPayments');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/{id}/show", name="showPayment", requirements={"id"="\d+"})
     */
    public function edit($id = null, ObjectManager $manager, Request $request, PaymentRepository $repo)
    {
        if($this->getUser()->getRole() != 3 ){

            $payment = $repo->find($id);
            if ($payment) {
                return $this->render('payment/show.html.twig', [
                    'payment' => $payment,
                    'connectedUser' => $this->getUser(),
                ]);
            } else {
                $request->getSession()->getFlashBag()->add('paymentError', 'Please select a payment to show more details!');
    
                return $this->redirectToRoute('allPayments');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function addPaymentSupplier(PaymentSupplier $paymentSupplier, Payment $payment, $price)
    {
        if ($paymentSupplier && $payment) {
            $priceToAdd = $paymentSupplier->getPrice() - $price;
            $payment->setTotalPricePaid($payment->getTotalPricePaid() + $priceToAdd)
                    ->setRemainingPrice($payment->getRemainingPrice() - $priceToAdd)
                    ->setTotalPricePaidToSupplier($payment->getTotalPricePaidToSupplier() + $priceToAdd)
                    ->setRemainigPriceToSupplier($payment->getRemainigPriceToSupplier() - $priceToAdd)
                    ->setFinished($payment->getRemainingPrice() == 0);

            return $payment;
        }
    }

    public function addPaymentDriver(PaymentDriver $paymentDriver, PaymentDriverRepository $paymentDriverRepo)
    {
        $pastPrice = 0;
        $pastDays = 0;
        if($paymentDriver && $paymentDriver->getId()){
            $pastPrice = $paymentDriverRepo->find($paymentDriver->getId())->getPrice();
            $pastDays = $paymentDriverRepo->find($paymentDriver->getId())->getDaysPaid();
        }
        $payment = $paymentDriver->getPayment();
        $priceToAdd = $paymentDriver->getPrice() - $pastPrice;
        $daysToAdd = $paymentDriver->getDaysPaid() - $pastDays;
        $payment->setTotalPricePaid($payment->getTotalPricePaid() + $priceToAdd)
            ->setRemainingPrice($payment->getRemainingPrice() - $priceToAdd)
            ->setTotalPricePaidToDriver($payment->getTotalPricePaidToDriver() + $priceToAdd)
            ->setRemainingPriceToDriver($payment->getRemainingPriceToDriver() - $priceToAdd)
            ->setRemainingDays($payment->getRemainingDays() - $daysToAdd)
            ->setTotalDaysPaid($payment->getTotalDaysPaid() + $daysToAdd)
            ->setFinished($payment->getRemainingPrice() == 0 && $payment->getRemainingDays() == 0);

        return $payment;
    }

    /**
     * @Route("/payment/search", name="exportPayments")
     */
    public function exportPayment(Request $request, PaymentDriverRepository $paymentDriverRepo, PaymentSupplierRepository $paymentSupplierRepo)
    {
        if($this->getUser()->getRole() != 3){
            $form = $this->createForm(ExportPaymentType::class);
            $form->handleRequest($request);
            if($form->isSubmitted()&&$form->isValid()){
                $data = $form->getData();
                if ($data['paymentOf']==1) {
                    $payments = $paymentDriverRepo->findByProjectAndDate($data);
                    return $this->print($data['project'], $payments, 'payment_driver.html.twig');
                }else{
                    $payments = $paymentSupplierRepo->findByProjectAndDate($data);
                    return $this->print($data['project'], $payments, 'payment_supplier.html.twig');
                }
                if(!$payments){
                    $request->getSession()->getFlashBag()->add('paymentMsg', 'There is no payments for the range of dates given');
                }
            }

            return $this->render('payment/export_data.html.twig', [
                'connectedUser' => $this->getUser(),
                'form' => $form->createView(),
            ]);
        }else{

            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/search/export", name="exportSearchedPayments")
     */
    public function print(Project $project, array $payments, $whereToGo)
    {
        if($this->getUser()->getRole()!=3 && $project){
            $fileName = (new \DateTime())->format('Hidmy');
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            
            // Instantiate Dompdf with our options
            $dompdf = new Dompdf($pdfOptions);
            
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('exportedFile/'.$whereToGo, [
                'project' => $project,
                'payments' => $payments,
            ]);
            
            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
            // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
            $dompdf->setPaper('A4', 'landscape');
    
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
