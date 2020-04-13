<?php

namespace Drupal\api_produto_hab\Model;

use Drupal\node\Entity\Node;

/**
 * Class VeiculoModel
 *
 * @package Drupal\api_produto_hab\Model
 */
class VeiculoModel
{
  /**
   * @param array $row
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createNode($row, $first_row)
  {
    $taxonomyModel = new TaxonomyModel();

    for($i=3; $i<=50; $i++){
      if($i<=10)            $motor_transm_cons[] = array('first' => $first_row[$i], 'second' => $row[$i]);
      if($i>10 && $i<=17)   $dimensoes[] = array('first' => $first_row[$i], 'second' => $row[$i]);
      if($i>17 && $i<=42)   $tracao_direcao_seg[] = array('first' => $first_row[$i], 'second' => $row[$i]);
      if($i>42)             $interno[] = array('first' => $first_row[$i], 'second' => $row[$i]);
    }

    $veiculoData = [
      'type'                              => 'veiculo',
      'title'                             => t('@model @version', ['@model' => $row[0], '@version' => $row[1]]),
      'field_veiculo_modelo'              => $taxonomyModel->getTidByNameAndVid($row[0], 'modelo'),
      'field_veiculo_versao'              => $taxonomyModel->getTidByNameAndVid($row[1], 'versoes'),
      'field_veiculo_preco_base'          => $row[2],
      'field_veiculo_motor_transm_cons'   => $motor_transm_cons,
      'field_veiculo_dimensoes'           => $dimensoes,
      'field_veiculo_tracao_direcao_seg'  => $tracao_direcao_seg,
      'field_veiculo_interno'             => $interno
    ];

    $revision = Node::create($veiculoData);
    $revision->save();
  }
  /**
   * @param array $row
   *
   * @return array
   */

  private function pluralize($term, $quantity) {
    if($quantity > 1)
      return $term . 's';

    return $term;
  }

  private function filterItems($items) {
    return array_filter(
            explode(',', $items)
          );
  }
}
