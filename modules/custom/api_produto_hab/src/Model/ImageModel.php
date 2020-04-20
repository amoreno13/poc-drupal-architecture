<?php

namespace Drupal\api_produto_hab\Model;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Class ImageModel
 *
 * @package Drupal\api_produto_hab\Model
 */
class ImageModel
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
  public function getImageByUri($uri, $alt)
  {
    // check first if the file exists for the uri    
    $files = \Drupal::entityTypeManager()
      ->getStorage('file')
      ->loadByProperties(['uri' => $uri]);
    $file = reset($files);
    
    // if not create a file
    if (!$file) {
      $file = File::create([
        'uri' => $uri,
      ]);
      $file->save();
    }

    return array(
      'target_id' => $file->id(),
      'alt' => $alt,
    );
  }
}
