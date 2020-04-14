<?php

namespace Drupal\api_produto_hab\Helper;

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class SpreadsheetHelper
 *
 * @package Drupal\api_produto_hab\Helper
 */
class SpreadsheetHelper
{
  /**
   * @param string $file
   *
   * @return array
   * @throws \PhpOffice\PhpSpreadsheet\Exception
   * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
   */
  public function readFile($file, $hasHeader)
  {
    // Load file
    $inputFileType = ucfirst(pathinfo($file)['extension']);
    $inputFileName = $file;
    $reader = IOFactory::createReader($inputFileType);
    $spreadsheet = $reader->load($inputFileName);
    $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
    
    if($hasHeader)  $return['header'] = $rows[0];
    unset($rows[0]);
    $return['rows'] = array_filter($rows, function($item){
      return $item[0] !== null;
    });

    return $return;
  }
}
