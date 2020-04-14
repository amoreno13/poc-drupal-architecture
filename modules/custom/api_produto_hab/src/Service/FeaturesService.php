<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\api_produto_hab\Service\ColorService;


class FeaturesService
{

  private $result;
  private $dataColors;

  public function sendRequest()
  {
    $colors = new ColorService();
    $this->dataColors = $colors->sendRequest();

    $nodes    = $this->getNodes('veiculo');
    $models   = $this->getTaxonomy('modelo');
    $versions =  $this->getTaxonomy('versoes');

    foreach ($nodes as $entity) {
      $model = $models[$entity->get('field_veiculo_modelo')->getString()];
      $version = $versions[$entity->get('field_veiculo_versao')->getString()];
      
      $this->setFeatures($entity, $model, $version);
    }

    return $this->result;
  }

  public function getNodes($content_type){
    $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', $content_type)
    ->execute();
  
    return \Drupal\node\Entity\Node::loadMultiple($query);
  }

  public function getTaxonomy($taxonomy){
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy);
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

  public function getMachineNames($type, $term, $removeTerm=false){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions($type, $term),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );
    foreach ($fields as $machine_name => $definition) {
      $machine_name_edited = $removeTerm ? str_replace($removeTerm,'',$machine_name) : $machine_name;
      $names[$definition->getType()][$machine_name_edited] = $machine_name;
    }
    
    return $names;
  }

  public function setFeatures($entity, $model, $version){
    $modelo         = str_replace('-','',strtolower($model));
    // $price          = $entity->get('field_veiculo_preco_base')->getString();
    // $thumbnail      = file_create_url($entity->get('field_veiculo_thumbnail')->entity->uri->value);
    
    $features_machine_name = $this->getMachineNames('node', 'veiculo');
    
    foreach($features_machine_name['double_field'] as $machine_name){
      foreach($entity->get($machine_name)->getValue() as $key => $values){
        $features[] = array(
          'category_label' => $entity->get($machine_name)->getFieldDefinition()->getLabel(),
          'feature_label' => $values['first'],
          'value' => $values['second']
        );
      }
    }

    $this->result[$modelo]['version_features'][] = array (
      'version' => $version,
      'colors' => $this->dataColors[$modelo][$version]['colors'],
      'features' => $features,
    );
  }
}
