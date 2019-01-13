<?php

namespace App\Controller;

use App\Entity\Mission;
use App\Entity\Allocate;
use App\Entity\Project;
use App\Entity\Supplier;
use App\Entity\Payment;
use App\Entity\PaymentSupplier;
use App\Form\PaymentSupplierType;
use App\Repository\AllocateRepository;
use App\Repository\SupplierRepository;
use App\Repository\MissionRepository;
use App\Repository\PaymentRepository;
use App\Repository\ProjectRepository;
use App\Form\ExportPaymentSupplierType;
use App\Repository\PaymentSupplierRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

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
            return $mission->getAllocate()->getPricePerDay() * ($mission->getAllocate()->getEndDate()->diff($mission->getAllocate()->getStartDate())->days+1);
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
        if($this->getUser()->getRole() != 3){

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
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/paymentSupplier/add/{idPayment}", name="addPaymentSupplier")
     * @Route("/payment/{idPayment}/paymentSupplier/{id}/edit", name="editPaymentSupplier", requirements={"id"="\d+"})
     */
    public function action(PaymentSupplier $paymentSupplier=null, PaymentSupplierRepository $repo, ObjectManager $manager, Request $request, AllocateRepository $rentRepo, SupplierRepository $supplierRepo, PaymentRepository $paymentRepo, $idPayment=null){
        if($this->getUser()->getRole() != 3){

            $payment = $paymentRepo->find($idPayment);
            if($payment || $paymentSupplier){
            $params = $this->testParams($request, $payment, $paymentSupplier);
            $form = $this->createForm(PaymentSupplierType::class, $params['paymentSupplier']);
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                if($this->comparePrice($params['paymentSupplier'], $request, $params['payment'], $params['price'])){
                    $paymentSupplier = $this->completeDatas($params['paymentSupplier'], $params['payment'], $rentRepo, $supplierRepo);
                    $this->save($manager, $paymentSupplier, $params['payment'], $request, $params['price']);
                    return $this->redirectToRoute("paymentSupplierByPayment", ['idPayment' => $params['payment']->getId(),]);
                }else{
                    $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "The price given to that supplier is more than his remaining expenses!");
                    }
                }
                    return $this->render('payment/paymentSupplierForm.html.twig', [
                        'connectedUser' => $this->getUser(),
                        'form' => $form->createView(),
                        'paymentSupplier' => $params['paymentSupplier'],
                    ]);
            }else{
                $request->getSession()->getFlashBag()->add('paymentMsg', "Please select a payment from the list below to link the new payment!");
                return $this->redirectToRoute('allPayments');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function completeDatas(PaymentSupplier $paymentSupplier, Payment $payment, AllocateRepository $rentRepo, SupplierRepository $supplierRepo){
        if($paymentSupplier && !$paymentSupplier->getId()){
            $paymentSupplier->setAllocate($rentRepo->findOneByPayment($payment))
                            ->setSupplier($supplierRepo->findByMission($payment->getMission()))
                            ->setPayment($payment)
                            ->setTotalPriceToPay($payment->getTotalPriceToPayToSupplier());
            }
        return $paymentSupplier;
    }

    /**
     * @Route("/payment/paymentSupplier/{id}/show", name="showPaymentSupplier")
     */
    public function showDetails(PaymentSupplier $paymentSupplier=null, Request $request){
        if($this->getUser()->getRole() != 3){

            if($paymentSupplier && $paymentSupplier->getId()){
                return $this->render('payment/paymentSupplierShow.html.twig', [
                    'connectedUser' => $this->getUser(),
                    'paymentSupplier' => $paymentSupplier,
                ]);
            }
                $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "Please select a payment from the lise below to show details!");
                return $this->redirectToRoute("allPaymentsSupplier");
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    /**
     * @Route("/payment/paymentSupplier/{id}/delete", name="deletePaymentSupplier")
     */
    public function delete(PaymentSupplier $paymentSupplier=null, ObjectManager $manager, Request $request, PaymentSupplierRepository $repo){
        if($this->getUser()->getRole() != 3){

            if($paymentSupplier&&$paymentSupplier->getId()){
                $payment = $this->addMoneyToPayment($paymentSupplier);
                $manager->remove($paymentSupplier);
                $manager->persist($manager->merge($payment));
                $manager->flush();
                $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "The payment was successfully deleted!");
                $request->getSession()->clear();
            }else{
                $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "There is no selected payment to delete!");
            }
            return $this->redirectToRoute('allPaymentsSupplier');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function addMoneyToPayment(PaymentSupplier $paymentSupplier){
        if($paymentSupplier && $paymentSupplier->getPayment()){
            $payment = $paymentSupplier->getPayment();
            $payment->setRemainingPrice($payment->getRemainingPrice()+$paymentSupplier->getPrice())
                    ->setRemainigPriceToSupplier($payment->getRemainigPriceToSupplier() + $paymentSupplier->getPrice())
                    ->setTotalPricePaid($payment->getTotalPricePaid()-$paymentSupplier->getPrice())
                    ->setTotalPricePaidToSupplier($payment->getTotalPricePaidToSupplier()-$paymentSupplier->getPrice())
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

    public function save(ObjectManager $manager, PaymentSupplier $paymentSupplier, Payment $payment, Request $request, $price){
        $payment = PaymentController::addPaymentSupplier($paymentSupplier, $payment, $price);
        $manager->persist($manager->merge($payment));
        $manager->persist($manager->merge($paymentSupplier));
        $manager->flush();
        $request->getSession()->clear();
        $request->getSession()->getFlashBag()->add('paymentSupplierMsg', $this->messageOfAction($paymentSupplier));
    }

    public function comparePrice(PaymentSupplier $paymentSupplier, Request $request, Payment $payment, $price){
        if ($paymentSupplier && $paymentSupplier->getId() && $request->attributes->get('_route') == "editPaymentSupplier") {
            $cond = $paymentSupplier->getPrice() <= $price + $payment->getRemainigPriceToSupplier();
        }else {
            $cond = $paymentSupplier->getPrice() <= $payment->getRemainigPriceToSupplier();
        }
        return $cond;
    }

    /**
     * @Route("/payment/paymentSupplier/cancel", name="cancelAddPaymentSupplier")
     */
    public function cancelAdd(Request $request){
        if($this->getUser()->getRole() != 3){

            $request->getSession()->clear();
            $request->getSession()->getFlashBag()->add('paymentSupplierMsg', "The process is cancled by the user!");
            return $this->redirectToRoute('allPaymentsSupplier');
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function testParams(Request $request, Payment $payment, PaymentSupplier $paymentSupplier=null){
        if ($paymentSupplier && $paymentSupplier->getId() && $request->attributes->get('_route') == "editPaymentSupplier") {
            $price = $paymentSupplier->getPrice();
            $payment = $paymentSupplier->getPayment();
        } else {
            $paymentSupplier = new PaymentSupplier();
            $price = 0;
        }
        return [
            'price' => $price,
            'payment' => $payment,
            'paymentSupplier' => $paymentSupplier,
        ];
    }

    /**
     * @Route("/supplier/payment/export", name="exportPaymentSupplier")
     */
    public function exportPayment(Request $request, MissionRepository $missionRepo, PaymentSupplierRepository $paymentSupplierRepo)
    {
        if($this->getUser()->getRole() != 3){
            $form = $this->createForm(ExportPaymentSupplierType::class);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                $data = $form->getData();
                $managedData = $this->manageData($missionRepo->findBySupplierAndProject($data['project'], $data['supplier']), $paymentSupplierRepo);
                return $this->print($data['project'], $data['supplier'], $managedData);
            }
            return $this->render('supplier/exportPayment.html.twig', [
                'form' => $form->createView(),
                'connectedUser' => $this->getUser(),
            ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function manageData($missions, PaymentSupplierRepository $paymentSupplierRepo)
    {
        $managedData = [];
        foreach ($missions as $mission) {
            $vehicle = $mission->getAllocate()->getVehicle();
            $key = $vehicle->getMatricule()." ".$vehicle->getBrand()." ".$vehicle->getType()->getName();
            $managedData[$key] = $paymentSupplierRepo->findByPayment($mission->getPayment());
        }
        return $managedData;
    }

    public function print(Project $project, Supplier $supplier, array $data)
    {
        if($this->getUser()->getRole()!=3 && $project){
            $fileName = (new \DateTime())->format('Hidmy');
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            
            // Instantiate Dompdf with our options
            $dompdf = new Dompdf($pdfOptions);
            
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('exportedFile/payment_supplier.html.twig', [
                'project' => $project,
                'supplier' => $supplier,
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
