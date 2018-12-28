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

        return $this->render('invoice/index.html.twig', array('invoices' => $invoices));
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

        return $this->redirectToRoute('invoice_show', array('id' => $invoice->getId()));
    }

    /**
     * @Route("invoice/new", name="invoice_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $invoice = new Invoice();
        $printSide = $this->get('session')->get('print-side');
        $printSide = $printSide->refreshFromDatabase($em);
        $invoice->setAmounts($printSide->getTotalAmount())
            ->setLiters($printSide->getTotalLiters())
            ->setIsPaid(false)
            ->setCreatedAt(new \DateTime());
        $form = $this->createForm(InvoiceType::class, $invoice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->setCreatedAt(new \DateTime());
            $reconciliations = $this->get('session')->get('print-side')->getAllReconciliations();
            $em = $this->getDoctrine()->getManager();
            $reconciliations = $em->getRepository('App:FuelReconciliation')->findReconciliationsByIds($reconciliations);
            $invoice->setReconciliations($reconciliations);
            $this->exportToExcel($invoice);
            $this->exportToPdf($invoice);
            $em->persist($invoice);
            $em->flush();

            return $this->redirectToRoute('invoice_show', array('id' => $invoice->getId()));
        }

        return $this->render('invoice/new.html.twig', array(
            'invoice' => $invoice,
            'form' => $form->createView(),
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
        ));
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
    public function exportToPdf(Invoice $invoice)
    {
        $html2pdf = new Html2Pdf();
        $printSide = $this->get('App\Service\ExcelGenerator')->getPrintSide();
        $html = $this->renderView('fuel_reconciliation/pdf_print.twig', array(
            'printSide' => $printSide,
            'invoice' => $invoice,
            )
        );
        $html2pdf->writeHTML($html);
        $html2pdf->output($this->getParameter('kernel.project_dir').'/web/pdfs/'.$invoice->getNumber().'_'.$invoice->getExcelFile().'.pdf', 'F');
    }

    private function exportToExcel(Invoice $invoice)
    {
        $spreadsheet = $this->get('App\Service\ExcelGenerator')->generateExcel($invoice->getNumber());
        $writer = new Xlsx($spreadsheet);
        $writer->save('spreadsheets/'.$invoice->getNumber().'_'.$invoice->getExcelFile().'.xls');
    }
}
