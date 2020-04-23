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
    $taxonomy_term  = 'modelo';
    $models         = $this->getTaxonomy($taxonomy_term);  
    $machine_names  = $this->getMachineNames($taxonomy_term);
    $this->setModelInfo($models, $machine_names);
    
    return $this->result;
  }

  public function getTaxonomy($taxonomy){
    $terms =\Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($taxonomy);
    foreach ($terms as $term) {
      $termFields = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
      $term_data[$term->name] = $termFields;
    }
    return $term_data;
  }

  public function setModelInfo($models, $machine_names){
    foreach($models as $name => $term){ //passa por cada modelo da taxonomia
      $modelo = preg_replace('/[^A-Za-z]/', '', strtolower($name));
      $this->result[$modelo]['model']             = $name;
      $this->result[$modelo]['machine_name']      = $modelo;
      $this->result[$modelo]['marketing_name']    = 'Honda '.$name;
      $this->result[$modelo]['product_page_url']  = 'https://www.honda.com.br/automoveis/'.$modelo;
      
      foreach($machine_names as $type => $names){ //passa por cada tipo de elemento 
        foreach($names as $key => $machine_name){ //separa o nome da chave e o machine_name
          switch ($type) {
            case 'image':
              $image_uri = file_create_url($term->get($machine_name)->entity->uri->value);
              $taxonomy[$key] = $image_uri;
              $this->result[$modelo][$key] = $image_uri;
            break;
            default:
              $value = $term->get($machine_name)->value;
              $this->result[$modelo][$key] = $value;
            break;
          }
        }
      }
    }
  }

  public function getMachineNames($term){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions('taxonomy_term', $term),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );
    foreach ($fields as $machine_name => $definition) {
      $names[$definition->getType()][str_replace('field_modelo_','',$machine_name)] = $machine_name;
    }
    
    return $names;
  }

}
