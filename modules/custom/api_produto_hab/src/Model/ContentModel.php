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
    $nid = \Drupal::entityQuery('node')
      ->condition('title', $name)
      ->condition('type', $vid)
      ->execute();

    if (!empty($nid)) {
      return $nid;
    } else {
      $node = Node::create([
        'type'        => $vid,
        'title'       => $name,
      ]);
      $node->save();
      $nid = $node->id();

      return $nid;
    }
  }
}
