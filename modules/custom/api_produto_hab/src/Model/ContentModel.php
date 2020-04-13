<?php

namespace Drupal\api_produto_hab\Model;

use Drupal\taxonomy\Entity\Term;

/**
 * Class ContentModel
 *
 * @package Drupal\api_produto_hab\Model
 */
class ContentModel
{
  /**
   * @param string $name
   * @param int $vid
   *
   * @return int|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getTidByNameAndVid($name, $vid)
  {
    $name = ucfirst(trim($name));

    // Set properties
    $properties = [];
    $properties['vid'] = $vid;
    $properties['name'] = $name;

    // Load term
    $terms = \Drupal::entityManager()->getStorage('node')->loadByProperties($properties);
    $term = reset($terms);

    if (!empty($term)) {
      return $term->id();
    } else {
      $_term = Term::create(['vid' => $vid, 'name' => $name]);
      $_term->save();
      $id = $_term->id();

      return $id;
    }
  }
}
