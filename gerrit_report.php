#!/usr/bin/php -f
<?php

/*
 * Gerrit report generator. Put it in cron, and
 * You will be getting .xlsx reports on your mail.
 *
 * @author Ruslan Oprits
 */

set_include_path('./include');

require __DIR__ . '/vendor/autoload.php';
require_once 'Settings.php';
require_once 'TeamsClass.php';
require_once 'GerritClient.php';

// PHPExcel class initialisation
$pExcel = new PHPExcel();
$pExcel->setActiveSheetIndex(0);
$aSheet = $pExcel->getActiveSheet();
$aSheet->setTitle(GERRIT_HOST);

$teams = new Teams();
//print_r($teams->teams_obj);

$gerrit = new GerritClient();
$row_number = 2;
foreach($gerrit->get_objects() as $gerrit_object){
    $row_number++;
    $result = $gerrit->stats($gerrit_object, $teams);
    print_r($result);
    
    $aSheet->setCellValue('A'.$row_number, $result->URL);
    $aSheet->getCell('A'.$row_number)->getHyperlink()->setUrl($result->URL);
    $aSheet->setCellValue('B'.$row_number, $result->LastUpdated);
    $aSheet->setCellValue('C'.$row_number, $result->MergeReady);
    $aSheet->setCellValue('D'.$row_number, $result->Blockers);
    $aSheet->setCellValue('E'.$row_number, $result->VRIF_score);
    $aSheet->setCellValue('F'.$row_number, $result->VRIF_total);
    $aSheet->setCellValue('G'.$row_number, $result->CRVW_score);
    $aSheet->setCellValue('H'.$row_number, $result->CRVW_total);
    $aSheet->setCellValue('I'.$row_number, $result->CRVW_ti);
    $aSheet->setCellValue('J'.$row_number, $result->Team);
    $aSheet->setCellValue('K'.$row_number, $result->Owner);
    $aSheet->setCellValue('L'.$row_number, $result->Email);
    $aSheet->setCellValue('M'.$row_number, $result->Branch);
    $aSheet->setCellValue('N'.$row_number, $result->Subject);

    /* Conditional Formatting ;) */
    if ('YES' == $result->MergeReady)
    $aSheet->getStyle('A'.$row_number.':N'.$row_number)
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('AAFFAA');

    elseif (! empty($result->Blockers))
        $aSheet->getStyle('A'.$row_number.':N'.$row_number)
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFAAAA');
    
    else 
        $aSheet->getStyle('A'.$row_number.':N'.$row_number)
            ->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF99');
    
}

/* Set Column Titles */
$aSheet->mergeCells('A1:A2');
$aSheet->setCellValue('A1','URL');
$aSheet->mergeCells('B1:B2');
$aSheet->setCellValue('B1',"Last\nUpdated");
$aSheet->mergeCells('C1:C2');
$aSheet->setCellValue('C1',"Merge\nReady");
$aSheet->mergeCells('D1:D2');
$aSheet->setCellValue('D1','Blockers');
$aSheet->mergeCells('E1:F1');
$aSheet->setCellValue('E1','Verified');
$aSheet->setCellValue('E2','Score');
$aSheet->setCellValue('F2','Total');
$aSheet->mergeCells('G1:I1');
$aSheet->setCellValue('G1','Code Review');
$aSheet->setCellValue('G2','Score');
$aSheet->setCellValue('H2','Total');
$aSheet->setCellValue('I2','by TI');
$aSheet->mergeCells('J1:J2');
$aSheet->setCellValue('J1','Team');
$aSheet->mergeCells('K1:K2');
$aSheet->setCellValue('K1','Owner');
$aSheet->mergeCells('L1:L2');
$aSheet->setCellValue('L1','Email');
$aSheet->mergeCells('M1:M2');
$aSheet->setCellValue('M1','Branch');
$aSheet->mergeCells('N1:N2');
$aSheet->setCellValue('N1','Subject');

/* Set Column Width */
$aSheet->getColumnDimension('A')->setWidth(21);
$aSheet->getColumnDimension('B')->setWidth(8);
$aSheet->getColumnDimension('C')->setWidth(8);
$aSheet->getColumnDimension('D')->setWidth(15);
$aSheet->getColumnDimension('E')->setWidth(5);
$aSheet->getColumnDimension('F')->setWidth(5);
$aSheet->getColumnDimension('G')->setWidth(5);
$aSheet->getColumnDimension('H')->setWidth(5);
$aSheet->getColumnDimension('I')->setWidth(5);
$aSheet->getColumnDimension('J')->setWidth(8);
$aSheet->getColumnDimension('K')->setWidth(22);
$aSheet->getColumnDimension('L')->setWidth(22);
$aSheet->getColumnDimension('M')->setWidth(12);
$aSheet->getColumnDimension('n')->setWidth(45);


/*** Define Styles ***/ 

/* Table Body */
$styleArrayBody = array(
    'font' => array(
        'name'=>'Arial',
        'size'=>'10',
        'bold'=>false,
    ),
    'alignment' => array(
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
    ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ),
    ),
);

/* Table Head */
$styleArrayHead = array(
    'font' => array(
        'name'=>'Arial',
        'size'=>'10',
        'bold'=>true,
    ),
    'alignment' => array(
        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
    ),
    'borders' => array(
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
        ),
    ),
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
            'argb' => 'CCCCFF',
        ),
    ),
);

/* Center only */
$styleCenter = array(
	'alignment'=>array(
		'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER
	)
);

/*** Apply Styling ***/

/* Table Head */
$aSheet->getStyle('A1:N2')
        ->applyFromArray($styleArrayHead);

/* Table Body */
$aSheet->getStyle('A3:N'.$row_number)
        ->applyFromArray($styleArrayBody);

/* Center */
$aSheet->getStyle('E3:J'.$row_number)
        ->applyFromArray($styleCenter);

$aSheet->getStyle('B3:C'.$row_number)
        ->applyFromArray($styleCenter);

/* Wrap text */
$aSheet->getStyle('N3:N'.$row_number)
    ->getAlignment()->setWrapText(true);

/* Autofilter. Multiple filters 
 * are not implemented in PHPExcel
 */
$aSheet->setAutoFilter('C1:C'.$row_number);
        

/* Freeze Pane */
$aSheet->freezePane('A3');

/* Write Excel */
include("PHPExcel/Writer/Excel2007.php");
$objWriter = new PHPExcel_Writer_Excel2007($pExcel);
$objWriter->save(REPORT_DIR.DIRECTORY_SEPARATOR.EXCEL_FILENAME);


/* Mail out */
$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
$mail->IsSMTP(); // telling the class to use SMTP

try {
  
  foreach ($MAIL_TO as $name => $email){
      $mail->AddAddress($email, $name);
  }
  
  foreach ($MAIL_CC as $name => $email){
      $mail->AddCC($email, $name);
  }
  
  $mail->Host = SMTP_HOST;
  $mail->SetFrom(current($MAIL_FROM), key($MAIL_FROM));
  $mail->Subject = MAIL_SUBJECT;
  $mail->Body = MSG_BODY;
  $mail->AddAttachment(REPORT_DIR.DIRECTORY_SEPARATOR.EXCEL_FILENAME);
    $mail->Send();
  echo "Message Sent OK\n";
} catch (phpmailerException $e) {
  echo $e->errorMessage(); //Pretty error messages from PHPMailer
} catch (Exception $e) {
  echo $e->getMessage(); //Boring error messages from anything else!
}

?>
