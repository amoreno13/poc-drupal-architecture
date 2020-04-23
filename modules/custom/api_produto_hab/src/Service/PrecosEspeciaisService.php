<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class PrecosEspeciaisService
{
  
  private $result;
  private $veiculo_machine_name;
  private $colors_machine_name;
  private $entities;

  public function sendRequest()
  {
    $this->entities = new EntitiesService;
    $this->setColorsFields();
    $this->setVeiculosFields();
    
    foreach ($this->entities->getNodes('colors') as $entity) {
      $precos[] = $this->getPrecos($entity);
    }
    
    $this->setPrecos($precos);

    return $this->result;
  }

  private function setColorsFields(){
    $this->colors_machine_name = array(
      'field_colors_veiculo'          => 'node', //content type veiculo
      'field_colors_weight'           => 'integer', 
      'field_colors_showcase_image'   => 'file',
      'field_colors_adicional_cor'    => 'decimal',
      'field_colors_cor'              => 'taxonomy_term', //taxonomy -> Cor
      'field_colors_legal'            => 'string',
      'field_colors_precos_especiais' => 'double_field',
    );
  }

  private function setVeiculosFields(){
    $this->veiculo_machine_name = array(
      'field_veiculo_modelo'          => 'taxonomy_term', //taxonomy -> Modelo
      'field_veiculo_preco_base'      => 'decimal', 
      'field_veiculo_versao'          => 'taxonomy_term',//taxonomy -> Versão
      'field_veiculo_highlights'      => 'string',
      'field_veiculo_weight'          => 'integer',
    );
  }  

  private function getPrecos($entity){
    foreach($this->colors_machine_name as $machine_name => $type){
      switch ($type){
        case 'node':
          $node = $this->entities->loadNode($entity, $machine_name);
          foreach($this->veiculo_machine_name as $node_machine_name => $node_type){
            switch ($node_type){
              case 'taxonomy_term':
                $term = $this->entities->loadTaxonomyTerm($node, $node_machine_name);
                $data[$node_machine_name] = $term['name'];
              break;
              default:
                $value = $this->entities->getFieldValue($node, $node_machine_name);
                if(is_array($value)) {
                  foreach($value as $v){
                    $data[$node_machine_name][] = $v;
                  }
                }
                else  $data[$node_machine_name] = $value;
            }
          }
        break;
        case 'taxonomy_term':
          $term = $this->entities->loadTaxonomyTerm($entity, $machine_name);
          $data[$machine_name]    = $term['name'];
          $data['field_cor_hex']  = $this->entities->getFieldValue($term['term'], 'field_cor_hex');
        break;
        case 'double_field':
          foreach($entity->get($machine_name)->getValue() as $key => $values){
            $data[$values['first']] = $values['second'] ? $values['second'] : '';
          }
        break;
        case 'file':
          $image_uri = file_create_url($entity->get($machine_name)->entity->uri->value);
          $data[$machine_name] = $image_uri;
        break;
        default:
          $data[$machine_name] = $this->entities->getFieldValue($entity, $machine_name);
      }
    }

    return $data;
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