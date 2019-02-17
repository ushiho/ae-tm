<?php

namespace App\Controller;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Entity\Project;
use App\Entity\Allocate;
use App\Entity\Invoice;



class ExcelController extends AbstractController{


    public function printMissions(Project $project)
    {
        $spreadsheet = $this->setProperties();
        $worksheet = $spreadsheet->getActiveSheet();        
        $worksheet->setTitle('Missions Of '.$project->getName());
        // set Columns width
        $worksheet = $this->setCellWidth($worksheet);
        $worksheet->getColumnDimension('A')->setWidth(18);
        $worksheet->getColumnDimension('I')->setWidth(26); // Note column


        $worksheet = $this->setFileHeader($worksheet, $project, 'I');
        
        // Start and end Date of the project
        $worksheet = $this->setDateOfProject($worksheet, $project, 'I');

        // The table's Headers
        $headers = ['A7'=> 'MISSION DATE','B7'=> 'BUDGET','C7'=> 'RENT DATE', 'D7'=> 'EXPENSES','E7'=>'LICENCE PLATE',
        'F7'=>'DRIVER','G7'=>'SALARY','H7'=>'DEPARTMENT', 'I7'=> 'NOTES'
        ];
        $worksheet = $this->setTableHeader($worksheet, $headers, 'I');

        $worksheet = $this->fillDataIn($worksheet, $project);
    
        $filename = time().'.xlsx';
        // Redirect output to a client's web browser (Xlsx)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: inline;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');
        
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function setProperties(){
      $spreadsheet = new Spreadsheet();
      $spreadsheet->getProperties()
        ->setCreator("AE Transportation Manager")
        ->setLastModifiedBy("AE Transportation Manager")
        ->setTitle("AE Transportation Manager - Invoice")
        ->setSubject("Mission Reconciliation")
        ->setDescription("Mission Reconciliation")
        ->setKeywords("Mission Reconciliation")
        ->setCategory("Invoicing");
        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    public function setCellWidth($worksheet){
        $worksheet->getColumnDimension('B')->setWidth(14);
        $worksheet->getColumnDimension('C')->setWidth(18);
        $worksheet->getColumnDimension('D')->setWidth(16);
        $worksheet->getColumnDimension('E')->setWidth(15);
        $worksheet->getColumnDimension('F')->setWidth(18);
        $worksheet->getColumnDimension('G')->setWidth(16);
        $worksheet->getColumnDimension('H')->setWidth(16);
        return $worksheet;
    }

    public function fillDataIn($worksheet, Project $project){
      $missions = $project->getMission();
      $row = 8;
      $styleArray = [
          'borders' => [
              'outline' => [
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                  'color' => ['argb' => '000000'],
              ],
              'inside' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['argb' => '000000'],
            ],
          ],
          'alignment' => [
              'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
              'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
          ],
      ];
      foreach ($missions as $mission) {
        $worksheet->getCell('A'.$row)->setValue($mission->getStartDate()->format('d/m/y').' - '.$mission->getEndDate()->format('d/m/y'));
        $worksheet->getCell('B'.$row)->setValue($mission->getPayment()->getTotalPrice().'DH');
        $worksheet->getCell('C'.$row)->setValue($mission->getAllocate()->getStartDate()->format('d/m/y').' - '.$mission->getAllocate()->getEndDate()->format('d/m/y'));
        $worksheet->getCell('D'.$row)->setValue($mission->getAllocate()->getPrice().'DH - '.$this->periodOfRent($mission->getAllocate()));
        $worksheet->getCell('E'.$row)->setValue($mission->getAllocate()->getVehicle()->getMatricule());
        $worksheet->getCell('F'.$row)->setValue($mission->getDriver()->getLastName().' '.$mission->getDriver()->getLastName());
        $worksheet->getCell('G'.$row)->setValue($mission->getSalaire().'DH - '.$this->periodOfWork($mission->getPeriodOfWork()));
        $worksheet->getCell('H'.$row)->setValue($mission->getDepartment()->getName());
        $worksheet->getCell('I'.$row)->setValue($mission->getNote());

        $worksheet->getStyle('A'.$row.':I'.$row)->applyFromArray($styleArray);
        $row +=1;
      }
    }

    public function periodOfRent(Allocate $rent){
      switch ($rent->getPeriod()) {
        case '1':
          return 'Dail';
        case '2':
          return 'Week';
        case '3':
          return 'Month';
      }
    }

    public function periodOfWork($period){
      switch ($period) {
        case '1':
          return 'Dail';
        case '2':
          return 'Week';
        case '3':
          return 'Month';
      }
    }

    private function setFileHeader($worksheet, Project $project, $endColumn){

        // Project Name and Styling
        $worksheet->getCell('A1')->setValue($project->getName());
        $worksheet->mergeCells('A1:'.$endColumn.'1');
        $worksheet->getStyle('A1:'.$endColumn.'1')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('44777b');
        $worksheet->getStyle('A1:'.$endColumn.'1')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(22)
                                    ->getColor()->setRGB('FFFFFF');
        $worksheet->getStyle('A1:'.$endColumn.'1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1:'.$endColumn.'1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('1')->setRowHeight('25');

        // AKram Number phone
        $worksheet->getCell('A2')->setValue('AKRAM EL KOUZOUZ  TRANSPORT MANAGER 0662737535');

        $worksheet->getStyle('A2:'.$endColumn.'2')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('A2:'.$endColumn.'2')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(14)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A2:'.$endColumn.'2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A2:'.$endColumn.'2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('2')->setRowHeight('22');
        return $worksheet;
    }

    private function setDateOfProject($worksheet, Project $project, $endColumn){
      $worksheet->getCell('G4')->setValue('From '.$project->getStartDate()->format('l d/m/y').' To '.$project->getEndDate()->format('l d/m/y'));
        $worksheet->getStyle('A4:'.$endColumn.'4')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('G4:'.$endColumn.'4')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(14)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A4:'.$endColumn.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A4:'.$endColumn.'4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('4')->setRowHeight('20');

        $worksheet->mergeCellsByColumnAndRow('1', '2', '10', '3');        
        $worksheet->mergeCellsByColumnAndRow('1', '5', '10', '6');
        return $worksheet;
    }

    private function setTableHeader($worksheet, $headersArray, $column){
      $worksheet->fromArray(
        $headersArray,
        null,
        'A7'
    );
    $worksheet->getStyle('A7:'.$column.'7')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF');
    $worksheet->getStyle('A7:'.$column.'7')->getFont()->setBold(true)
                                ->setName('Yrsa SemiBold')
                                ->setSize(14)
                                ->getColor()->setRGB('44777b');
    $worksheet->getStyle('A7:'.$column.'7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $worksheet->getStyle('A7:'.$column.'7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    $worksheet->getRowDimension('7')->setRowHeight('20');
    $styleArray = [
      'borders' => [
          'outline' => [
              'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
              'color' => ['argb' => '000000'],
          ],
          'inside' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            'color' => ['argb' => '000000'],
        ],
      ],
      'alignment' => [
          'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
          'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
      ],
  ];
    
    $worksheet->getStyle('A7:'.$column.'7')->applyFromArray($styleArray);
    return $worksheet;
    }


    public function printReconciliations(Invoice $invoice, Project $project, array $reconciliations, $fileName){
      $spreadsheet = $this->setProperties();
      $worksheet = $spreadsheet->getActiveSheet();        
      $worksheet->setTitle('Invoice N°'.$invoice->getId());
      // set Columns width
      $worksheet = $this->setCellWidth($worksheet);
      $worksheet->getColumnDimension('A')->setWidth(14);
      $worksheet->getColumnDimension('I')->setWidth(16);
      $worksheet->getColumnDimension('J')->setWidth(26);

      $worksheet = $this->setFileHeader($worksheet, $project, 'J');
      
      // Start and end Date of the project
      $worksheet->getCell('A4')->setValue($reconciliations[0]->getGasStation()->getName());
      $worksheet->getStyle('A4:J4')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(20)
                                    ->getColor()->setRGB('44777b');
      $worksheet = $this->setDateOfProject($worksheet, $project, 'J');
      

      // The table's Headers
      $headers = ['A7'=> 'RECEIPT','B7'=> 'DATE','C7'=> 'LICENCE PLATE', 'D7'=> 'VEHICLE','E7'=>'DEPARTMENT',
      'F7'=>'DRIVER','G7'=>'AMOUNT','H7'=>'KM', 'I7'=> 'LITERS', 'J7'=> 'NOTES',
      ];
      $worksheet = $this->setTableHeader($worksheet, $headers, 'J');

      $worksheet = $this->fillInvoiceData($invoice, $worksheet);
  
      // Redirect output to a client's web browser (Xlsx)
      // header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      // header('Content-Disposition: inline;filename="'.$fileName.'"');
      // header('Cache-Control: max-age=0');
      
      $writer = new Xlsx($spreadsheet);
      $writer->save('./invoice/excel/'.$fileName.'.xlsx');

      return;
    }

    private function fillInvoiceData(Invoice $invoice, $worksheet){
        $row = 8;
        $kilome = 0;
        $liters = 0;
        $amount = 0;
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
                'inside' => [
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                  'color' => ['argb' => '000000'],
              ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ];
        foreach ($invoice->getReconciliations() as $recon) {
          $worksheet->getCell('A'.$row)->setValue($recon->getReceiptNum());
          $worksheet->getCell('B'.$row)->setValue($invoice->getCreatedAt()->format('d/m/y'));
          $worksheet->getCell('C'.$row)->setValue($recon->getVehicle()->getMatricule());
          $worksheet->getCell('D'.$row)->setValue($recon->getVehicle()->getType()->getName());
          $worksheet->getCell('E'.$row)->setValue($recon->getDepartment()->getName());
          $worksheet->getCell('F'.$row)->setValue($recon->getDriver()->getLastName().' '.$recon->getDriver()->getLastName());
          $worksheet->getCell('G'.$row)->setValue($recon->getTotalAmount().' DH ');
          $worksheet->getCell('H'.$row)->setValue($recon->getKilometrage().' Km');
          $worksheet->getCell('I'.$row)->setValue($recon->getTotalLitres().' L');
          $worksheet->getCell('J'.$row)->setValue($recon->getNote());

          $worksheet->getStyle('A'.$row.':J'.$row)->applyFromArray($styleArray);
          $row +=1;
          $kilome += $recon->getKilometrage();
          $liters += $recon->getTotalLitres();
          $amount += $recon->getTotalAmount();
        }

        $worksheet->getCell('F'.$row)->setValue('TOTAL');
        $worksheet->getCell('G'.$row)->setValue($amount.' DH');
        $worksheet->getCell('H'.$row)->setValue($kilome.' Km');
        $worksheet->getCell('I'.$row)->setValue($liters.' L');
        return $this->totalLine($row, $worksheet);
    }

    private function totalLine($endRow, $worksheet){
        $worksheet->getStyle('A'.$endRow.':J'.$endRow)->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('A'.$endRow.':J'.$endRow)->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(16)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A'.$endRow.':J'.$endRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A'.$endRow.':J'.$endRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension($endRow)->setRowHeight('20');
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
                'inside' => [
                  'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                  'color' => ['argb' => '000000'],
              ],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
        ];
        $worksheet->getStyle('F'.$endRow.':I'.$endRow)->applyFromArray($styleArray);

        return $worksheet;
      }

}


?>