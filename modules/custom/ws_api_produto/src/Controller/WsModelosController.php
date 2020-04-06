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


class WsModelosController extends ControllerBase
{
  
  public function call(Request $request)
  {
    $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree('modelo');
    foreach ($terms as $term) {
      $data[] =  $term->name ;
    }
    
    return new JsonResponse($data);
  }

}
