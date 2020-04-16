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
  public function createNode($row, $header)
  {
    $taxonomyModel = new TaxonomyModel();

    $motor_transm_cons  = $this->getFeatures($header, $row, 9, 8);
    $dimensoes          = $this->getFeatures($header, $row, 17, 7);
    $tracao_direcao_seg = $this->getFeatures($header, $row, 24, 25);
    $interno            = $this->getFeatures($header, $row, 49, 8);

    $veiculoData = [
      'type'                              => 'veiculo',
      'title'                             => t('@model @version', ['@model' => $row[0], '@version' => $row[1]]),
      'field_veiculo_modelo'              => $taxonomyModel->getTidByNameAndVid($row[0], 'modelo'),
      'field_veiculo_versao'              => $taxonomyModel->getTidByNameAndVid($row[1], 'versoes'),
      'field_veiculo_weight'              => $row[2],
      'field_veiculo_preco_base'          => $row[3],
      'field_veiculo_highlights'          => array($row[4],$row[5],$row[6],$row[7],$row[8]),
      'field_veiculo_motor_transm_cons'   => $motor_transm_cons,
      'field_veiculo_dimensoes'           => $dimensoes,
      'field_veiculo_tracao_direcao_seg'  => $tracao_direcao_seg,
      'field_veiculo_interno'             => $interno
    ];

    $revision = Node::create($veiculoData);
    $revision->save();

    $this->updateReference($revision->id(),$row[0].' '.$row[1]);

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

  private function getFeatures($header, $row, $startRow, $length) {
    $Feature  = array_slice($header, $startRow, $length);
    $Value    = array_slice($row, $startRow, $length);
    foreach(array_combine($Feature, $Value) as $feature => $value)
      $array[] = array('first' => $feature, 'second' => $value);
    return $array;
  }

  private function updateReference($new_id, $title){
    $nids = \Drupal::entityQuery('node')->condition('type','colors')->condition('type','colors')->execute();
    foreach ($nids as $nid) {
      $node = \Drupal\node\Entity\Node::load($nid);
      if(strpos($node->title->value, $title.' ') !== false){
        $node->field_colors_veiculo->target_id = $new_id;
        $node->save();
      }
    }
  }
}
