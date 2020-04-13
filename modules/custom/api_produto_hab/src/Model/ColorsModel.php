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
      'title'                             => t('@veiculo @color', ['@veiculo' => $row[0], '@color' => $row[1]]),
      'field_colors_veiculo'              => $contentModel->getTidByNameAndVid($row[0], 'veiculo'),
      'field_colors_cor'                  => $taxonomyModel->getTidByNameAndVid($row[1], 'cor'),
      'field_colors_adicional_cor'        => $row[2],
      'field_colors_weight'               => $row[3],
      'field_colors_legal'                => $row[4],
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
