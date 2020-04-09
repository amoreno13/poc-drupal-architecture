<?php

namespace Drupal\ws_api_produto\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ColorService
{
  
  private $result;
  private $array_machine_names;

  public function sendRequest()
  {
    $this->array_machine_names = array(
      'field_colors_veiculo', //content type veiculo
      'field_colors_weight', 
      'field_colors_showcase_image',
      'field_colors_preco_adicional', //content type precos
      'field_colors_legal',
      'field_veiculo_modelo', //taxonomy -> Modelo
      'field_veiculo_preco_base', 
      'field_veiculo_versao',//taxonomy -> Versão
      'field_precos_cor', //taxonomy -> Cor
      'field_precos_modelo', //taxonomy -> Modelo
      'field_precos_valor_adicional_cor',
      'field_cor_hex'
    );
    
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
    $features_machine_name = $this->getMachineNames('colors'); 
    foreach($features_machine_name as $machine_name => $type){ 
      if($type == 'node'){
        $tid = $entity->get($machine_name)->getString();
        $node = \Drupal\node\Entity\Node::load($tid);
        $node_machine_names = $this->getMachineNames($node->getType());
        foreach($node_machine_names as $node_machine_name => $node_type){
          if($node_type == 'taxonomy_term'){
            if($node_machine_name == 'field_precos_cor') {
              $data['field_cor_hex'] = $node->get($node_machine_name)->first()->get('entity')->getTarget()->getValue()->get('field_cor_hex')->getString();
            }
            $data[$node_machine_name] = $node->get($node_machine_name)->first()->get('entity')->getTarget()->getValue()->label();
          }
          else{
            $data[$node_machine_name] = $node->get($node_machine_name)->first()->getString();
          }
        }
      }
      else {
        $data[$machine_name] = $entity->get($machine_name)->getString();
      }
    }
    
    return $data;
  }

  public function getMachineNames($term){
    $fields = array_filter(
      \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $term),
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
      if($array_features['field_veiculo_modelo'] == $array_features['field_precos_modelo']){
        $modelo = str_replace('-','',strtolower($array_features['field_veiculo_modelo']));
        $cor_machine_name = $this->RemoveSpecialChar($array_features['field_precos_cor']);
        $color_name = str_replace(' ','_',strtolower($cor_machine_name));
        $this->result[$modelo][$array_features['field_veiculo_versao']]['colors'][] = array(
          'name' => $array_features['field_precos_cor'],
          'machine_name' => $color_name,
          'showcase_image' => $array_features['field_colors_showcase_image'],
          'hex' => $array_features['field_cor_hex'],
          'weight' => $array_features['field_colors_weight'],
          'price_full' => $array_features['field_colors_preco_adicional'] + $array_features['field_veiculo_preco_base'],
          'legal'=> $array_features['field_colors_legal']
        );
      }
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