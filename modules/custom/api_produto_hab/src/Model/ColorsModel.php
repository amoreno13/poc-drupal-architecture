<?php

namespace Drupal\api_produto_hab\Model;

use Drupal\node\Entity\Node;

/**
 * Class ColorsModel
 *
 * @package Drupal\api_produto_hab\Model
 */
class ColorsModel
{
  /**
   * @param array $row
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createNode($row, $first_row)
  {
    $taxonomyModel  = new TaxonomyModel();
    $contentModel   = new ContentModel();

    $colorsData = [
      'type'                              => 'colors',
      'title'                             => t('@model @version @color', ['@model' => $row[0], '@version' => $row[1], '@color' => $row[2]]),
      'field_colors_veiculo'              => $contentModel->getTidByNameAndVid($row[0].' '.$row[1], 'veiculo'),
      'field_colors_cor'                  => $taxonomyModel->getTidByNameAndVid($row[2], 'cor'),
      'field_colors_adicional_cor'        => $row[3],
      'field_colors_weight'               => $row[4],
      'field_colors_legal'                => $row[5],
    ];

    $revision = Node::create($colorsData);
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
