<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ColorService
{
  
  private $result;
  private $array_machine_names;
  private $features_machine_name;

  public function sendRequest()
  {
    $this->array_machine_names = array(
      'field_colors_veiculo', //content type veiculo
      'field_colors_weight', 
      'field_colors_showcase_image',
      'field_colors_adicional_cor',
      'field_colors_cor', //taxonomy -> Cor
      'field_colors_legal',
      'field_veiculo_modelo', //taxonomy -> Modelo
      'field_veiculo_preco_base', 
      'field_veiculo_versao',//taxonomy -> Versão
      'field_cor_hex'
    );

    $this->features_machine_name = $this->getMachineNames('colors','node'); 
    $nodes_colors  = $this->getNodes('colors');

    foreach ($nodes_colors as $entity) { 
      $features[] = $this->getFeatures($entity);
    }
    
    $this->setFeatures($features);

    return $this->result;
  }

  public function getNodes($content_type){ 
    $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', $content_type)
    ->execute();

    return \Drupal\node\Entity\Node::loadMultiple($query);
  }

  public function getFeatures($entity){
    foreach($this->features_machine_name as $machine_name => $type){ 
      if($type == 'node'){
        $tid = $entity->get($machine_name)->getString();
        $node = \Drupal\node\Entity\Node::load($tid);
        $node_machine_names = $this->getMachineNames($node->getType(), 'node');
        foreach($node_machine_names as $node_machine_name => $node_type){
          if($node_type == 'taxonomy_term')
            $data[$node_machine_name] = $node->get($node_machine_name)->first()->get('entity')->getTarget()->getValue()->label();
          else
            $data[$node_machine_name] = $node->get($node_machine_name)->first()->getString();
        }
      }
      else if($type == 'taxonomy_term'){
        $tid = $entity->get($machine_name)->getString();
        $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($tid);
        $data[$machine_name] = $term->getName();
        $data['field_cor_hex'] = $term->get('field_cor_hex')->getString();
      }
      else {
        $data[$machine_name] = $entity->get($machine_name)->getString();
      }
    }
    return $data;
  }

  public function getMachineNames($term, $element_type){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions($element_type, $term),
      function ($fieldDefinition) {
        return $fieldDefinition instanceof \Drupal\field\FieldConfigInterface;
      }
    );
    
    foreach ($fields as $machine_name => $definition) { 
      $type = $definition->getSettings()['target_type'] ? $definition->getSettings()['target_type'] : $definition->getType();
      if(in_array($machine_name,$this->array_machine_names)) $names[$machine_name]= $type;
    }
    return $names;
  }
  
  function setFeatures($features){
    foreach($features as $key => $array_features){
      $modelo = str_replace(' ','',str_replace('-','',strtolower($array_features['field_veiculo_modelo'])));
      $cor_machine_name = $this->RemoveSpecialChar($array_features['field_colors_cor']);
      $color_name = str_replace(' ','_',strtolower($cor_machine_name));
      $this->result[$modelo][$array_features['field_veiculo_versao']]['colors'][] = array(
        'name' => $array_features['field_colors_cor'],
        'machine_name' => $color_name,
        'showcase_image' => $array_features['field_colors_showcase_image'],
        'hex' => $array_features['field_cor_hex'],
        'weight' => $array_features['field_colors_weight'],
        'price_full' => $array_features['field_colors_adicional_cor'] + $array_features['field_veiculo_preco_base'],
        'legal'=> $array_features['field_colors_legal']
      );
    }    
  }

  function RemoveSpecialChar($string){
    $unwanted_array = array(    
      'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
      'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
      'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
      'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
      'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    return strtr( $string, $unwanted_array );
  }
}