<?php

namespace Drupal\api_produto_hab\Helper;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * Class SpreadsheetFilterHelper
 *
 * @package Drupal\api_produto_hab\Helper
 */
class SpreadsheetFilterHelper implements IReadFilter
{
  private $startRow = 0;

  private $endRow = 0;

  private $columns = [];

  /**
   * SpreadsheetFilterHelper constructor.
   *
   * @param $startRow
   * @param $endRow
   * @param $columns
   */
  public function __construct($startRow, $endRow, $columns)
  {
    $this->startRow = $startRow;
    $this->endRow = $endRow;
    $this->columns = $columns;
  }

  /**
   * @param string $column
   * @param int    $row
   * @param string $worksheetName
   *
   * @return bool
   */
  public function readCell($column, $row, $worksheetName = '')
  {
    if ($row >= $this->startRow && $row <= $this->endRow) {
      if (in_array($column, $this->columns)) {
        return true;
      }
    }

    return false;
  }
}
