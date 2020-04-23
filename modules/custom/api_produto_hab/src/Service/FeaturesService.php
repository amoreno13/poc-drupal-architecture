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
  private $entities;

  public function sendRequest()
  {
    $colors = new ColorService();
    $this->dataColors = $colors->sendRequest();

    $this->entities = new EntitiesService;
    $nodes          = $this->entities->getNodes('veiculo');

    foreach ($nodes as $entity) {
      $model    = $this->entities->loadTaxonomyTerm($entity, 'field_veiculo_modelo')['name'];
      $version  = $this->entities->loadTaxonomyTerm($entity, 'field_veiculo_versao')['name'];
      
      $this->setFeatures($entity, $model, $version);
    }

    return $this->result;
  }

  public function getMachineNames($type, $term){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions($type, $term),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );
    foreach ($fields as $machine_name => $definition) {
      $names[$definition->getType()][$machine_name] = $machine_name;
    }
    
    return $names;
  }

  public function setFeatures($entity, $model, $version){
    $modelo = preg_replace('/[^A-Za-z]/', '', strtolower($model));
    
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
