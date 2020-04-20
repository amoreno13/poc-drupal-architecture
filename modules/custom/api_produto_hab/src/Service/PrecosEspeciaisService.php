<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class PrecosEspeciaisService
{
  
  private $result;
  private $array_machine_names;
  private $precos_machine_name;

  public function sendRequest()
  {
    $this->array_machine_names = array(
      'field_colors_veiculo', //content type veiculo
      'field_colors_weight', 
      'field_colors_showcase_image',
      'field_colors_adicional_cor',
      'field_colors_cor', //taxonomy -> Cor
      'field_colors_legal',
      'field_colors_precos_especiais',
      'field_veiculo_modelo', //taxonomy -> Modelo
      'field_veiculo_preco_base', 
      'field_veiculo_versao',//taxonomy -> Versão
      'field_veiculo_highlights',
      'field_veiculo_weight',
      'field_cor_hex'
    );

    $this->precos_machine_name = $this->getMachineNames('colors','node'); 
    $nodes_precos  = $this->getNodes('colors');
    
    foreach ($nodes_precos as $entity) {
      $precos[] = $this->getPrecos($entity);
    }

    $this->setPrecos($precos);

    return $this->result;
  }

  private function getNodes($content_type){ 
    $query = \Drupal::entityQuery('node')
    ->condition('status', 1)
    ->condition('type', $content_type)
    ->execute();
    return \Drupal\node\Entity\Node::loadMultiple($query);
  }

  private function getPrecos($entity){
    foreach($this->precos_machine_name as $machine_name => $type){
      if($type == 'node'){
        $tid = $entity->get($machine_name)->getString();
        $node = \Drupal\node\Entity\Node::load($tid);
        $node_machine_names = $this->getMachineNames($node->getType(), 'node');
        foreach($node_machine_names as $node_machine_name => $node_type){
          if($node_type == 'taxonomy_term')
            $data[$node_machine_name] = $node->get($node_machine_name)->first()->get('entity')->getTarget()->getValue()->label();
          else if($node_machine_name == 'field_veiculo_highlights'){
            foreach($node->get($node_machine_name)->getValue() as $key => $highlights){
              $data[$node_machine_name][] = $highlights['value'];
            }
          }
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
      else if($type == 'double_field'){
        foreach($entity->get($machine_name)->getValue() as $key => $values){
          $data[$values['first']] = $values['second'] ? $values['second'] : '';
        }
      }
      else if($type == 'file'){
        $image_uri = file_create_url($entity->get($machine_name)->entity->uri->value);
        $data[$machine_name] = $image_uri;
      }
      else {
        $data[$machine_name] = $entity->get($machine_name)->getString();
      }
    }
    return $data;
  }

  private function getMachineNames($term, $element_type){
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
  
  private function setPrecos($precos){
    foreach($precos as $key => $array_data){
      $modelo = $array_data['field_veiculo_modelo'];
      $modelo_raw = preg_replace('/[^A-Za-z]/', '', strtolower($modelo));
      $versao = $array_data['field_veiculo_versao'];
      $versao_raw = preg_replace('/[^A-Za-z]/', '', strtolower($versao));
      $cor_machine_name = str_replace(' ','_',strtolower($array_data['field_colors_cor']));
      
      $array_colors = array(
        'name' => $array_data['field_colors_cor'],
        'machine_name' => $this->RemoveSpecialChar($cor_machine_name),
        'showcase_image' => $array_data['field_colors_showcase_image'],
        'hex' => $array_data['field_cor_hex'],
        'weight' => $array_data['field_colors_weight'],
        'price_full' => number_format($array_data['field_veiculo_preco_base'] + $array_data['field_colors_adicional_cor'],2,',','.'),
        'legal' => $array_data['field_colors_legal'],
      );

      foreach ($array_data as $machine_name => $value){
        if(strpos($machine_name, 'price_discount') !== false){
          if($value)  $array_colors[$machine_name] = $value;
        }
      }

      $colors[$modelo_raw]['versions'][$versao_raw]['colors'][] = $array_colors;

      $versions[$modelo_raw]['versions'][$versao_raw] = array(
        'name' => $versao,
        'machine_name' => $versao_raw,
        'marketing_name' => 'Honda ' . $modelo . ' '. $versao,
        'weight' => $array_data['field_veiculo_weight'],
        'highlights' => $array_data['field_veiculo_highlights'],
        'lead_form_url' => 'https://www.honda.com.br/automoveis/tenho-interesse',
        'lead_model_info' => $modelo . ' - ' . $versao,
      );
    }

    $data = array_merge_recursive($versions, $colors);

    foreach($data as $modelo => $versions){
      foreach ($versions as $version => $data){
        $result[$modelo]['versions'] = array_values($data);
      }
    }
    
    $this->result = $result;
  }

  private function RemoveSpecialChar($string){
    $unwanted_array = array(    
      'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
      'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
      'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
      'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
      'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
    return strtr( $string, $unwanted_array );
  }
}