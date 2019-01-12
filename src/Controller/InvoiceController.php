<?php

namespace App\Controller;

use App\Entity\Invoice;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use App\Repository\InvoiceRepository;
use App\Form\InvoiceType;
use App\Entity\FuelReconciliation;
use App\Repository\FuelReconciliationRepository;
use App\Repository\VehicleRepository;
use Doctrine\Common\Persistence\ObjectManager;


class InvoiceController extends Controller
{
    /**
     * @Route("/invoice", name="invoice_index")
     * @Method("GET|POST")
     */
    public function indexAction(Request $request, InvoiceRepository $repo)
    {
        $invoices = array();
        if ($request->getMethod() == 'POST') {
            $criterias = array();
            if ($request->get('number')) {
                $criterias['number'] = $request->get('number');
            }
            if ($request->get('date')) {
                $criterias['dateCreation'] = \DateTime::createFromFormat('Y-m-d', $request->get('date'));
            }
            $invoices = $repo->findBy($criterias);
        } else {
            $invoices = $repo->findAll();
        }

        return $this->render('invoice/index.html.twig', array(
            'invoices' => $invoices,
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("invoice/mark-as-paid/{id}", name="invoice_as_paid")
     * @Method("GET")
     */
    public function markAsPaid(Invoice $invoice)
    {
        $invoice->setIsPaid(true);
        foreach ($invoice->getReconciliations() as $reconciliation) {
            $reconciliation->setIsPayed(true);
        }
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('invoice_show', array(
            'id' => $invoice->getId(),
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("invoice/new", name="invoice_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, FuelReconciliationRepository $fuelRecoRepo, VehicleRepository $vehicleRepo)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new Invoice();
        $printSide = $this->get('session')->get('print-side');
        if (!$printSide) {
            $request->getSession()->getFlashBag()->add('fuelMsg', 'Please select the reconciliations to create the related invoice.');

            return $this->redirectToRoute('searchFuelReconciliation');
        }
        $printSide = $printSide->refreshFromDatabase($fuelRecoRepo);
        $invoice->setTotalAmounts($printSide->getTotalAmount())
            ->setTotalLitres($printSide->getTotalLiters())
            ->setIsPaid(false)
            ->setCreatedAt(new \DateTime());
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setCreatedAt(new \DateTime());
            $reconciliations = $this->get('session')->get('print-side')->getAllReconciliations();
            $em = $this->getDoctrine()->getManager();
            $reconciliations = $em->getRepository('App:FuelReconciliation')->findReconciliationsByIds($reconciliations);
            $invoice->setReconciliation($reconciliations);
            $this->exportToExcel($invoice, $fuelRecoRepo, $vehicleRepo);
            $this->exportToPdf($invoice, $fuelRecoRepo);
            $em->persist($invoice);
            $em->flush();

            return $this->redirectToRoute('invoice_show', array(
                'id' => $invoice->getId(),
                'connectedUser' => $this->getUser(),
            ));
        }

        return $this->render('invoice/new.html.twig', array(
            'invoice' => $invoice,
            'form' => $form->createView(),
            'connectedUser' => $this->getUser(),
        ));
    }

    /**
     * @Route("invoice/show/{id}", name="invoice_show", requirements={"id"="\d+"}))
     * @Method("GET")
     */
    public function showAction(Invoice $invoice)
    {
        $deleteForm = $this->createDeleteForm($invoice);

        return $this->render('invoice/show.html.twig', array(
            'invoice' => $invoice,
            'delete_form' => $deleteForm->createView(),
            'connectedUser' => $this->getUser(),        ));
    }

    /**
     * Creates a form to delete a fuelReconciliation entity.
     *
     * @param FuelReconciliation $invoice The fuelReconciliation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Invoice $invoice)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('invoice_delete', array('id' => $invoice->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Deletes a fuelReconciliation entity.
     *
     * @Route("invoice/delete/{id}", name="invoice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Invoice $invoice)
    {
        $form = $this->createDeleteForm($invoice);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->remove($invoice);
        $em->flush();

        return $this->redirectToRoute('invoice_index');
    }

    /**
     * Finds and displays a fuelReconciliation entity.
     *
     * @Route("invoice/export-to-pdf/{id}", name="export_invoice_pdf", requirements={"id"="\d+"}))
     * @Method("GET")
     */
    public function exportToPdf(Invoice $invoice, FuelReconciliationRepository $fuelRecoRepo)
    {
        $html2pdf = new Html2Pdf();
        $printSide = $this->get('App\Service\ExcelGenerator')->getPrintSide($fuelRecoRepo);
        $html = $this->renderView('fuel_reconciliation/pdf_print.twig', array(
            'printSide' => $printSide,
            'invoice' => $invoice,
            )
        );
        $html2pdf->writeHTML($html);
        $html2pdf->output($this->getParameter('kernel.project_dir').'/web/pdfs/'.$invoice->getNumber().'_'.$invoice->getExcelFile().'.pdf', 'F');
    }

    private function exportToExcel(Invoice $invoice, FuelReconciliationRepository $fuelRecoRepo, VehicleRepository $vehicleRepo)
    {
        $spreadsheet = $this->get('App\Service\ExcelGenerator')->generateExcel($invoice->getNumber(), $fuelRecoRepo, $vehicleRepo);
        $writer = new Xlsx($spreadsheet);
        $writer->save('spreadsheets/'.$invoice->getNumber().'_'.$invoice->getExcelFile().'.xls');
    }

    public function init($reconciliations, Request $request, $fileName, ObjectManager $manager)
    {
        $invoice = new Invoice();
        $invoice->setCreatedAt(new \DateTime())
                ->setTotalAmounts($request->getSession()->get('totalAmount'))
                ->setTotalLitres($request->getSession()->get('totalLitres'))
                ->setExcelFile($fileName)
                // ->setReconciliation($reconciliations)
                ->setIsPaid(false);
        
        
        return $invoice;
    }
}
