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
use Drupal\ws_api_produto\Service\ModelosService;
use Drupal\ws_api_produto\Service\FeaturesService;

class WsCompleteAPIController extends ControllerBase
{
  public function call(Request $request)
  {
    $modelos = new ModelosService();
    $models = $modelos->sendRequest();
    
    $features = new FeaturesService();
    $result = array_merge_recursive($models,$features->sendRequest());

    return new JsonResponse($result);
  }
}