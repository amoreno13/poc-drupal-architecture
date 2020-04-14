<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ModelosService
{

  private $result;

  public function sendRequest()
  {
    $models = $this->getTaxonomy('modelo');    
    $this->setModelInfo($models);
    
    return $this->result;
  }

  public function getTaxonomy($taxonomy){
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy);
    foreach ($terms as $term) {
      $term_data[$term->tid] = $term->name;
    }
    return $term_data;
  }

  public function setModelInfo($models){
    $machine_names = $this->getMachineNames('modelo', 'field_modelo_');
    foreach($models as $tid => $name){ //passa por cada modelo da taxonomia
      $modelo = preg_replace('/[^A-Za-z]/', '', strtolower($name));
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
      $this->result[$modelo]['model']             = $name;
      $this->result[$modelo]['machine_name']      = $modelo;
      $this->result[$modelo]['marketing_name']    = 'Honda '.$name;
      $this->result[$modelo]['product_page_url']  = 'https://www.honda.com.br/automoveis/'.$modelo;
    }
  }

  public function getMachineNames($term, $removeTerm=false){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions('taxonomy_term', $term),
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

}
