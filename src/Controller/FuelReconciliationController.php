<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\FuelReconciliationRepository;
use App\Entity\FuelReconciliation;
use App\Form\FuelReconciliationType;
use App\Model\PrintSide;
use App\Entity\Mission;
use App\Controller\InvoiceController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use App\Form\SearchReconciliationType;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Invoice;
use App\Repository\PaymentRepository;
use App\Repository\SupplierRepository;
use App\Repository\MissionRepository;
use App\Repository\AllocateRepository;
use App\Repository\DriverRepository;
use App\Repository\InvoiceRepository;
// Include Dompdf required namespaces
use Dompdf\Dompdf;
use Dompdf\Options;

class FuelReconciliationController extends AbstractController
{
    /**
     * @Route("/fuel/reconciliation", name="all_fuel_reconciliation")
     */
    public function index(FuelReconciliationRepository $repo, Request $request)
    {
        if (!$this->testRole()) {
            return $this->toProfil($request);
        }
        $fuelReconciliations = $repo->findAll();

        return $this->render('fuel_reconciliation/fuelReconciliationBase.html.twig', array(
            'fuelReconciliations' => $fuelReconciliations,
            'connectedUser' => $this->getUser(),
            ));
    }

    /**
     * @Route("fuel/reconciliation/search", name="searchFuelReconciliation")
     */
    public function searchAction(Request $request, FuelReconciliationRepository $repo, MissionRepository $missionRepo)
    {
        if (!$this->testRole()) {
            return $this->toProfil($request);
        }
        $form = $this->createForm(SearchReconciliationType::class);
        $form->handleRequest($request);
        $res = [];
        $subTotals = 0;
        $total = 0;
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $driverID = ($data['driver'] !== null) ? $data['driver']->getId() : 0;
            $vehicleID = ($data['vehicle'] !== null) ? $data['vehicle']->getId() : 0;
            $departmentID = ($data['department'] !== null) ? $data['department']->getId() : 0;
            $projectID = ($data['project'] !== null) ? $data['project']->getId() : 0;
            $firstDate = $data['firstDate'];
            $secondDate = $data['secondDate'];
            $gasStation = $data['gasStation'];
            $isPaid = $data['isPaid'];
            $res = $repo->getReconciliations($driverID, $vehicleID, $departmentID, $projectID, $firstDate, $secondDate, $isPaid, $gasStation);
            $subTotals = $repo->getSubTotals();
            $total = $repo->getTotal();
        }

        return $this->render('fuel_reconciliation/search.html.twig', array(
            'form' => $form->createView(),
            'list' => $res,
            'subTotals' => $subTotals,
            'total' => $total,
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("fuel/reconciliation/new", name="addFuelReconciliation")
     *  @Route("fuel/reconciliation/edit/{id}", name="editFuelReconciliation", requirements={"id" = "\d+"})
     * @Method({"GET", "POST"})
     */
    public function newAction(FuelReconciliation $fuelReconciliation = null, Request $request, ObjectManager
     $manager)
    {
        if (!$this->testRole()) {
            return $this->toProfil($request);
        }
        if (!$fuelReconciliation) {
            $fuelReconciliation = new Fuelreconciliation();
        }
        $form = $this->createForm(FuelReconciliationType::class, $fuelReconciliation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($fuelReconciliation->getVehicle() != null || $fuelReconciliation->getDriver() != null) {
                $fuelReconciliation = $this->completeDatas($fuelReconciliation, $manager);
                if (!$fuelReconciliation) {
                    $request->getSession()->getFlashBag()->add('fuelMsg', 'No mission linked to this driver/vehicle.');

                    return $this->render('fuel_reconciliation/new.html.twig', array(
                        'fuelReconciliation' => $fuelReconciliation,
                        'form' => $form->createView(),
                        'connectedUser' => $this->getUser(),
                        'fuelReconciliation' => $fuelReconciliation,
                    ));
                }
                $manager->persist($fuelReconciliation);
                $manager->flush();
                

                return $this->redirectToRoute('show_fuel_reconciliation', array('id' => $fuelReconciliation->getId()));
            } else {
                $request->getSession()->getFlashBag()->add('fuelMsg', 'You must enter the vehicle or driver information.');
            }
        }

        return $this->render('fuel_reconciliation/new.html.twig', array(
            'fuelReconciliation' => $fuelReconciliation,
            'form' => $form->createView(),
            'connectedUser' => $this->getUser(),
            'fuelReconciliation' => $fuelReconciliation,
        ));
    }

    /**
     * @Route("/fuel/reconciliation/show/{id}", name="show_fuel_reconciliation", requirements={"id"= "\d+"})
     * @Method("GET")
     */
    public function showAction(FuelReconciliation $fuelReconciliation = null, Request $request)
    {
        if (!$this->testRole()) {
            return $this->toProfil($request);
        }

        return $this->render('fuel_reconciliation/show.html.twig', array(
            'fuelReconciliation' => $fuelReconciliation,
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("fuel/reconciliation/delete/{id}", name="deleteFuelReconciliation")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, FuelReconciliation $fuelReconciliation = null, ObjectManager $manager)
    {
        if (!$this->testRole()) {
            return $this->toProfil($request);
        }
        $manager->remove($fuelReconciliation);
        $manager->flush();

        return $this->redirectToRoute('all_fuel_reconciliation');
    }



    /**
     * @Route("fuel/reconciliation/remove-reconciliation/{id}", name="remove_reconciliation_from_print_side",options={"expose"=true})
     * @Method("GET")
     */
    public function removeReconciliationFromPrintSideAction(FuelReconciliation $reconciliation)
    {
        $session = $this->get('session');

        $printSide = $session->get('print-side');
        $_printSide = $printSide->removeReconciliation($reconciliation);
        $session->set('print-side', $printSide);

        return $this->redirectToRoute('print_side');
    }


    public function testRole()
    {
        return $this->getUser()->getRole() == 2 ? false : true;
    }

    public function toProfil(Request $request)
    {
        $request->getSession()->getFlashBag()->add('profilMsg', "You don't have access.");

        return $this->redirectToRoute('profil');
    }

    public function completeDatas(FuelReconciliation $fuelReconciliation, ObjectManager $manager)
    {
        $mission = $this->testVehicleAndDriverToSearchForMission($fuelReconciliation, $manager);
        if ($mission) {
            return  $fuelReconciliation->setCreatedAt(new \DateTime())
                                ->setIsPaid(false)
                                ->setDepartment($mission->getDepartment())
                                ->setDriver($mission->getDriver())
                                ->setVehicle($mission->getAllocate()->getVehicle())
                                ->setProject($mission->getProject())
                                ->setUser($this->getUser())
                                ->setInvoice(null)
                                ->setMission($mission);
        }
    }

    public function testVehicleAndDriverToSearchForMission(FuelReconciliation $fuelReconciliation, ObjectManager $manager)
    {
        if ($fuelReconciliation->getDriver()) {
            $mission = $manager->getRepository(Mission::class)->findOneByDriver($fuelReconciliation->getDriver());
        } elseif ($fuelReconciliation->getVehicle()) {
            $mission = $manager->getRepository(Mission::class)->findOneByVehicle($fuelReconciliation->getVehicle());
        } else {
            $mission = null;
        }

        return $mission;
    }

    /**
     * @Route("fuel/reconciliation/make/invoice", name="makeAsFactured")
     */
    public function makeAsFactured(Request $request, ObjectManager $manager, InvoiceRepository $invoiceRepo)
    {
        if($this->getUser()->getRole() != 3){
            $reconciliations = $request->getSession()->get('reconciliations');
            if($reconciliations){
                $fileName = (new \DateTime())->format('Hidmy');
                $id = $invoiceRepo->findMaxId() + 1;
                $invoice = InvoiceController::init($reconciliations, $request, $fileName, $manager, $id);
                $manager->persist($invoice);
                $manager->flush();
                foreach ($reconciliations as $item) {
                    $item->setInvoice($invoice);
                    $invoice->addReconciliation($item);
                    $RAW_QUERY = "UPDATE fuel_reconciliation f SET f.invoice_id = ".$id." WHERE f.id = ".$item->getId()." ;" ;
                    $statement = $manager->getConnection()->prepare($RAW_QUERY);
                    $statement->execute();
                }
        
                $request->getSession()->clear();
                $request->getSession()->getFlashBag()->add('InvoiceMsg', 'Your invoice created successfully!');
                $this->printExcelFile($reconciliations, $invoice, $fileName);
                $this->print($reconciliations, $fileName);
                return $this->redirectToRoute('invoice_show', [
                    'id' => $id,
                ]);
            }else{
                $request->getSession()->getFlashBag()->add('fuelMsg', "Please search for reconciliations to create an invoice.");
                return $this->redirectToRoute('searchFuelReconciliation');
            }
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function print(array $reconciliations, $fileName)
    {
        if($this->getUser()->getRole()!=3){
            
            // Configure Dompdf according to your needs
            $pdfOptions = new Options();
            $pdfOptions->set('defaultFont', 'Arial');
            
            // Instantiate Dompdf with our options
            $dompdf = new Dompdf($pdfOptions);
            
            // Retrieve the HTML generated in our twig file
            $html = $this->renderView('exportedFile/invoice.html.twig', [
                'reconciliations' => $reconciliations,
            ]);
            
            // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
            $dompdf->setPaper('A4', 'portrait');

            // Load HTML to Dompdf
            $dompdf->loadHtml($html);
            
    
            // Render the HTML as PDF
            $dompdf->render();
    
            $output = $dompdf->output();
            file_put_contents('invoice/pdf/'.$fileName.'.pdf', $output);
            // Output the generated PDF to Browser (force download)
            // $dompdf->stream($fileName.".pdf", [
            //     "Attachment" => false,
            // ]);
        }else{
            return $this->redirectToRoute('error403');
        }
    }

    public function printExcelFile(array $reconciliations, $invoice, $fileName){
        $excelController = new ExcelController();
        $project = $reconciliations[0]->getProject();
        $excelController->printReconciliations($invoice, $project, $reconciliations, $fileName);
        return;
    }
}
