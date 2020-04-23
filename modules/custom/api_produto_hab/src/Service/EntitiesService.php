<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;


class EntitiesService
{

  public function loadTaxonomyTerm($entity, $machine_name)
  {
    $tid = $entity->get($machine_name)->getString();
    $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
    
    return array(
      'name' => $term->getName(),
      'term' => $term
    );
  }

  public function getNodes($content_type){
    $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', $content_type)
    ->execute();

    return \Drupal\node\Entity\Node::loadMultiple($query);
  }

  public function loadNode($entity, $machine_name){
    $tid = $entity->get($machine_name)->getString();
    return \Drupal\node\Entity\Node::load($tid);
  }

  public function getFieldValue($entity, $machine_name){
    foreach ($entity->get($machine_name)->getValue() as $key => $array){
      foreach ($array as $value){
        $resp[] = $value;
      }
    }
    if(count($resp) == 1) $resp = $resp[0];
    return $resp;
  }
}