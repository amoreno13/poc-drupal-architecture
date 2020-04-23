<?php

namespace Drupal\api_produto_hab\Service;

use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class ColorService
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
      $colors[] = $this->getColors($entity);
    }
    
    $this->setColors($colors);

    return $this->result;
  }

  function setColorsFields(){
    $this->colors_machine_name = array(
      'field_colors_veiculo'        => 'node', //content type veiculo
      'field_colors_weight'         => 'integer', 
      'field_colors_thumbnail'      => 'file',
      'field_colors_adicional_cor'  => 'decimal',
      'field_colors_cor'            => 'taxonomy_term', //taxonomy -> Cor
      'field_colors_legal'          => 'string',
    );
  }

  function setVeiculosFields(){
    $this->veiculo_machine_name = array(
      'field_veiculo_modelo'        => 'taxonomy_term', //taxonomy -> Modelo
      'field_veiculo_preco_base'    => 'decimal', 
      'field_veiculo_versao'        => 'taxonomy_term',//taxonomy -> Versão
    );
  }

  public function getColors($entity){
    foreach($this->colors_machine_name as $machine_name => $type){
      switch ($type) {
        case 'node':
          $node = $this->entities->loadNode($entity, $machine_name);
          foreach($this->veiculo_machine_name as $node_machine_name => $node_type){
            if($node_type == 'taxonomy_term'){
              $term = $this->entities->loadTaxonomyTerm($node, $node_machine_name);
              $data[$node_machine_name] = $term['name'];
            }
            else{
              $data[$node_machine_name] = $this->entities->getFieldValue($node, $node_machine_name);
            }
          }
        break;
        case 'taxonomy_term':
          $term = $this->entities->loadTaxonomyTerm($entity, $machine_name);
          $data[$machine_name]    = $term['name'];
          $data['field_cor_hex']  = $this->entities->getFieldValue($term['term'], 'field_cor_hex');
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
  
  function setColors($colors){
    foreach($colors as $key => $array_colors){
      $modelo = preg_replace('/[^A-Za-z]/', '', strtolower($array_colors['field_veiculo_modelo']));
      $cor_machine_name = $this->RemoveSpecialChar($array_colors['field_colors_cor']);
      $color_name = str_replace(' ','_',strtolower($cor_machine_name));
      
      $this->result[$modelo][$array_colors['field_veiculo_versao']]['colors'][] = array(
        'name' => $array_colors['field_colors_cor'],
        'machine_name' => $color_name,
        'showcase_image' => $array_colors['field_colors_thumbnail'],
        'hex' => $array_colors['field_cor_hex'],
        'weight' => $array_colors['field_colors_weight'],
        'price_full' => number_format($array_colors['field_veiculo_preco_base'] + $array_colors['field_colors_adicional_cor'],2,',','.'),
        'legal'=> $array_colors['field_colors_legal']
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