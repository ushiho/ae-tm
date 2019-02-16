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



class ExcelController extends AbstractController{


    public function printMissions(Project $project)
    {
        $spreadsheet = $this->setProperties();
        $worksheet = $spreadsheet->getActiveSheet();        
        $worksheet->setTitle('Missions Of project_name');
        // set Columns width
        $worksheet = $this->setFileHeader($worksheet, $project);
        
        // Start and end Date of the project
        $worksheet->getCell('G4')->setValue('From '.$project->getStartDate()->format('l d/m/y').' To '.$project->getEndDate()->format('l d/m/y'));
        $worksheet->getStyle('A4:I4')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('A4:I4')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(14)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A4:I4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A4:I4')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('4')->setRowHeight('20');

        $worksheet->mergeCellsByColumnAndRow('1', '2', '9', '3');        
        $worksheet->mergeCellsByColumnAndRow('1', '5', '9', '6'); 

        // The table's Headers
        $worksheet->fromArray(
          ['A7'=> 'MISSION DATE','B7'=> 'BUDGET','C7'=> 'RENT DATE', 'D7'=> 'EXPENSES','E7'=>'LICENCE PLATE',
          'F7'=>'DRIVER','G7'=>'SALARY','H7'=>'DEPARTMENT', 'I7'=> 'NOTES'
          ],
          null,
          'A7'
      );
      $worksheet->getStyle('A7:I7')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('A7:I7')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(14)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A7:I7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A7:I7')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
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
      
      $worksheet->getStyle('A7:I7')->applyFromArray($styleArray);

      $worksheet = $this->fillDataIn($worksheet, $project, $styleArray);
    
    

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
      $worksheet->getColumnDimension('A')->setWidth(18);
        $worksheet->getColumnDimension('B')->setWidth(14);
        $worksheet->getColumnDimension('C')->setWidth(18);
        $worksheet->getColumnDimension('D')->setWidth(16);
        $worksheet->getColumnDimension('E')->setWidth(15);
        $worksheet->getColumnDimension('F')->setWidth(18);
        $worksheet->getColumnDimension('G')->setWidth(16);
        $worksheet->getColumnDimension('H')->setWidth(16);
        $worksheet->getColumnDimension('I')->setWidth(26);
        return $worksheet;
    }

    public function fillDataIn($worksheet, Project $project, $styleArray){
      $missions = $project->getMission();
      $row = 8;
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

    private function setFileHeader($worksheet, Project $project){
      $worksheet = $this->setCellWidth($worksheet);
        // Project Name and Styling
        $worksheet->getCell('A1')->setValue($project->getName());
        $worksheet->mergeCells('A1:I1');
        $worksheet->getStyle('A1:I1')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('44777b');
        $worksheet->getStyle('A1:I1')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(22)
                                    ->getColor()->setRGB('FFFFFF');
        $worksheet->getStyle('A1:I1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A1:I1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('1')->setRowHeight('25');

        // AKram Number phone
        $worksheet->getCell('A2')->setValue('AKRAM EL KOUZOUZ  TRANSPORT MANAGER 0662737535');

        $worksheet->getStyle('A2:I2')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FFFFFF');
        $worksheet->getStyle('A2:I2')->getFont()->setBold(true)
                                    ->setName('Yrsa SemiBold')
                                    ->setSize(14)
                                    ->getColor()->setRGB('44777b');
        $worksheet->getStyle('A2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $worksheet->getStyle('A2:I2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $worksheet->getRowDimension('2')->setRowHeight('22');
        return $worksheet;
    }

}


?>