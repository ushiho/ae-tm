<?php

namespace App\Service;

use App\Entity\FuelReconciliation;
use App\Entity\Project;
use App\Model\PrintSide;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ExcelGenerator
{
    private $currentLine = 0;
    private $currentCulumn = 0;
    private $currentProject;
    private $currentReconciliation;
    private $currentReconciliationCount = 0;

    private $em;
    private $session;

    public function __construct(EntityManagerInterface $em, SessionInterface $session)
    {
        $this->em = $em;
        $this->session = $session;
    }

    /**
     * @return Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function generateExcel($number)
    {
        $printSide = $this->getPrintSide();
        $spreadsheet = new Spreadsheet();
        $spreadsheet->setActiveSheetIndex(0);
        $spreadsheet->getDefaultStyle()->getFont()
            ->setName('Arial')
            ->setSize(11)
            ->setBold(true)
            ->setColor(new Color(Color::COLOR_BLACK));
        $spreadsheet->getActiveSheet()->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $spreadsheet->getActiveSheet()->getPageSetup()
            ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $this->addFileMetadata($spreadsheet);
        $sheet = $spreadsheet->getActiveSheet();
        $this->addPageHeader($sheet, $printSide, $number);
        $this->addTableHeader($sheet);
        $this->currentCulumn = 1;
        $this->currentLine = 6;
        foreach ($printSide->getProjects() as $project) {
            $startDate = $printSide->getProjectEarlierReconciliation($project);
            $subTotal = $printSide->getSubTotals()[$project->getId()];
            $subLitersTotal = $printSide->getSubLitersTotals()[$project->getId()];
            $this->addProjectLines($sheet, $project, $startDate, $subTotal, $subLitersTotal);
            $this->currentProject = $project;
        }
        ++$this->currentLine;
        $sheet->setBreak('A'.$this->currentLine, Worksheet::BREAK_ROW);
        ++$this->currentLine;
        $this->addInvoiceTotalLine($sheet, $printSide->getTotalAmount(), $printSide->getTotalLiters());

        return $spreadsheet;
    }

    public function getPrintSide()
    {
        $session = $this->session;
        $printSide = $session->has('print-side') ? $session->get('print-side') : new PrintSide();
        $refershedPrintSide = $printSide->refreshFromDatabase($this->em);
        $refershedPrintSide->sortDates();

        return $refershedPrintSide;
    }

    private function addFileMetadata(Spreadsheet $spreadsheet)
    {
        $spreadsheet->getProperties()
            ->setCreator('AE Transportation Manager')
            ->setLastModifiedBy('AE Transportation Manager')
            ->setTitle('AE Transportation Manager - Invoice')
            ->setSubject('Fuel Station Reconciliation')
            ->setDescription('Fuel Station Reconciliation')
            ->setKeywords('Fuel Station Reconciliation')
            ->setCategory('Invoicing');

        return $spreadsheet;
    }

    /**
     * @param Worksheet $sheet
     * @param PrintSide $prinSide
     *
     * @return Worksheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addPageHeader(Worksheet $sheet, PrintSide $prinSide, $number)
    {
        $sheet->getRowDimension('1')->setRowHeight(30);
        $sheet->getDefaultColumnDimension()->setWidth(12);
        $sheet->getDefaultRowDimension()->setRowHeight(16);
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1:L1')->getFont()->setSize(20);
        $sheet->getStyle('A1:L1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:L1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A2:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A4:E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getColumnDimension('D')->setWidth(20);
        $this->setCellsRangsColorAndBorder($sheet, array('A2:B2', 'B2:D2', 'A3:B3', 'B3:D3', 'A4:B4', 'B4:D4'), '99ff33');
        $sheet->setCellValue('A1', 'FUEL STATION RECONCILIATION');
        $sheet->mergeCells('A2:B2')->mergeCells('C2:D2')
            ->setCellValue('A2', 'Gaz Station')
            ->setCellValue('C2', strtoupper($prinSide->getGasStation()));
        $sheet->mergeCells('A3:B3')->mergeCells('C3:D3')
            ->setCellValue('A3', 'START DATE')
            ->setCellValue('C3', strtoupper($prinSide->getStartDate()->format('l d/m/Y')));

        $sheet->mergeCells('A4:B4')->mergeCells('C4:D4')
            ->setCellValue('A4', 'END DATE')
            ->setCellValue('C4', strtoupper($prinSide->getEndDate()->format('l d/m/Y')));
        $sheet->setBreak('A5', Worksheet::BREAK_ROW);
        $project = $prinSide->getProjects()->first()->getReconciliations()[0]->getProject();
        $sheet->setCellValue('I2', 'Project : '.$project->getName())->mergeCells('I2:J2');
        $sheet->setCellValue('I5', 'N° : '.$number)->mergeCells('I2:J2');
        $sheet->setCellValue('I3', 'AKRAM ELKOUZOUZ')->mergeCells('I3:J3');
        $sheet->setCellValue('I4', '+212 662-737535')->mergeCells('I4:J4');
        $sheet->getStyle('F')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('G')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $sheet->getStyle('H')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);

        return $sheet;
    }

    /**
     * @param Worksheet $sheet
     *
     * @return Worksheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addTableHeader(Worksheet $sheet)
    {
        $sheet->fromArray(
            ['A6' => 'DATE', 'B6' => 'N°', 'C6' => 'LICENCE PLATE', 'D6' => 'DRIVER\'S NAME', 'E6' => 'DEPARTMENT',
            'F6' => 'KM', 'G6' => 'LITRES', 'H6' => 'AMOUNT', 'I6' => 'VEHICLE-TYPE', 'J6' => 'REMARKS',
            ],
            null,
            'A6'
        );
        $this->setCellsRangsColorAndBorder(
            $sheet,
            array('A6', 'B6', 'C6', 'D6', 'E6', 'F6', 'G6', 'H6', 'I6', 'J6'),
            '959996');

        $sheet->getStyle('A6:J6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(17);
        $sheet->getColumnDimension('I')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(17);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->mergeCells('J6:K6');

        return $sheet;
    }

    private function addProjectLines(Worksheet $sheet, Project $project, \DateTime $startDate, $subtotal, $subTotalLiters)
    {
        $this->currentLine = $this->currentLine + 1;
        $this->addProjectHeader($sheet, $project, $startDate);
        foreach ($project->getReconciliations() as $reconciliation) {
            ++$this->currentLine;
            $this->addReconciliationLine($sheet, $reconciliation);
        }
        ++$this->currentLine;
        $this->addProjectTotalLine($sheet, $subtotal, $subTotalLiters);

        return $sheet;
    }

    private function addProjectHeader(Worksheet $sheet, Project $project, \DateTime $startDate)
    {
        $cellsrang = 'A'.$this->currentLine.':F'.$this->currentLine;
        $sheet->mergeCells($cellsrang);
        $this->setCellsRangsColorAndBorder($sheet, array($cellsrang), 'ff6666');
        $sheet->setCellValue('A'.$this->currentLine, strtoupper($startDate->format('d/m/Y')));

        return $sheet;
    }

    private function addProjectTotalLine(Worksheet $sheet, $projectTotal, $projectLitersTotal)
    {
        $i = $this->currentLine;
        $cellsrang = 'A'.$i.':F'.$i;
        $sheet->mergeCells($cellsrang);
        $this->setCellsRangsColorAndBorder($sheet, array($cellsrang), '78a5ed');
        $this->setCellsRangsColorAndBorder($sheet, array('G'.$i, 'H'.$i), '78a5ed');
        $sheet->setCellValue('A'.$this->currentLine, 'SUBTOTAL');
        $sheet->setCellValue('G'.$this->currentLine, $projectLitersTotal);
        $sheet->setCellValue('H'.$this->currentLine, $projectTotal);
        $sheet->getStyle('A'.$this->currentLine.':H'.$this->currentLine)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$this->currentLine.':H'.$this->currentLine)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        return $sheet;
    }

    /**
     * @param Worksheet          $sheet
     * @param FuelReconciliation $reconciliation
     *
     * @return Worksheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    private function addReconciliationLine(Worksheet $sheet, FuelReconciliation $reconciliation)
    {
        $sheet->fromArray(
            array(
                'A'.$this->currentLine => $reconciliation->getDateCreation()->format('d/m/Y'),
                'B'.$this->currentLine => $reconciliation->getUiid(),
                'C'.$this->currentLine => $reconciliation->getVehicle()->getMat(),
                'D'.$this->currentLine => $reconciliation->getDriver()->getFirstName().' '.$reconciliation->getDriver()->getLastName(),
                'E'.$this->currentLine => $reconciliation->getDepartment()->getName(),
                'F'.$this->currentLine => $reconciliation->getKilometerage(),
                'G'.$this->currentLine => $reconciliation->getLiters(),
                'H'.$this->currentLine => $reconciliation->getAmount(),
                'I'.$this->currentLine => strtoupper($reconciliation->getVehicle()->getType()),
                'J'.$this->currentLine => $reconciliation->getRemarks(),
            ),
            null,
            'A'.$this->currentLine
        );

        $sheet->getStyle('A'.$this->currentLine.':J'.$this->currentLine)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$this->currentLine.':J'.$this->currentLine)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->mergeCells('J'.$this->currentLine.':K'.$this->currentLine);

        return $sheet;
    }

    private function addInvoiceTotalLine(Worksheet $sheet, $total, $totalLiters)
    {
        $i = $this->currentLine;
        $cellsrang = 'A'.$i.':F'.$i;
        $sheet->mergeCells($cellsrang);
        $this->setCellsRangsColorAndBorder($sheet, array($cellsrang), '2eb8b8');
        $this->setCellsRangsColorAndBorder($sheet, array('G'.$i, 'H'.$i), '2eb8b8');
        $sheet->setCellValue('A'.$this->currentLine, 'TOTAL');
        $sheet->setCellValue('G'.$this->currentLine, $totalLiters);
        $sheet->setCellValue('H'.$this->currentLine, $total);
        $sheet->getStyle('A'.$this->currentLine.':H'.$this->currentLine)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A'.$this->currentLine.':H'.$this->currentLine)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('A'.$this->currentLine.':L'.$this->currentLine)->getFont()->setSize(15);
        $sheet->getRowDimension($this->currentLine)->setRowHeight(25);

        return $sheet;
    }

    private function setCellsRangsColorAndBorder($sheet, $rangs, $color)
    {
        foreach ($rangs as $rang) {
            $sheet->getStyle($rang)->getFill()->applyFromArray([
                'fillType' => Fill::FILL_GRADIENT_LINEAR,
                'rotation' => 0,
                'endColor' => [
                    'argb' => $color,
                ],
            ]);

            $sheet->getStyle($rang)->getBorders()->getRight()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle($rang)->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle($rang)->getBorders()->getLeft()->setBorderStyle(Border::BORDER_MEDIUM);
            $sheet->getStyle($rang)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);
        }
    }
}
