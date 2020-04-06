<?php

/**
 * Created by PhpStorm.
 * User: mariozuany
 * Date: 27/08/2016
 * Time: 21:00 h
 */

namespace Drupal\ws_api_produto\Controller;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class WsCompleteAPIController extends ControllerBase
{
  private $result;

  public function call(Request $request)
  {
    $nodes  = $this->getNodes('veiculo');
    $models = $this->getTaxonomy('modelo');
    
    $this->setModelInfo($models);

    foreach ($nodes as $entity) {
      $model = $models[$entity->get('field_veiculo_modelo')->getString()];
      $version = taxonomy_term_load($entity->get('field_veiculo_versao')->getString())->getName();
      
      $this->setFeatures($entity, $model, $version);
    }

    return new JsonResponse($this->result);
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

  public function setModelInfo($models){
    $machine_names = $this->getMachineNames('taxonomy_term', 'modelo', 'field_modelo_');
    foreach($models as $tid => $name){ //passa por cada modelo da taxonomia
      $modelo = str_replace('-','',strtolower($name));
      foreach($machine_names as $type => $names){ //passa por cada tipo de elemento 
        foreach($names as $key => $machine_name){ //separa o nome da chave e o machine_name
          $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
          if($type == 'image'){
            $image_uri = file_create_url($term->get($machine_name)->entity->uri->value);
            $taxonomy[$key] = $image_uri;
            $this->result[$modelo][$key] = $image_uri;
          }
          else{
            $value = $term->get($machine_name)->value;
            $this->result[$modelo][$key] = $value;
          }
        }
      }
      $this->result[$modelo]['model'] = $name;
      $this->result[$modelo]['machine_name'] = $modelo;
      $this->result[$modelo]['marketing_name'] = 'Honda '.$name;
      $this->result[$modelo]['product_page_url'] = 'https://www.honda.com.br/automoveis/'.$modelo;
    }
  }

  public function setFeatures($entity, $model, $version){
      $modelo = str_replace('-','',strtolower($model));
      $price      = $entity->get('field_veiculo_preco_base')->getString();
      $thumbnail  = file_create_url($entity->get('field_veiculo_thumbnail')->entity->uri->value);
      
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
        'price' => $price,
        'thumbnail'=> $thumbnail,
        'features' => $features
      );
  }

}