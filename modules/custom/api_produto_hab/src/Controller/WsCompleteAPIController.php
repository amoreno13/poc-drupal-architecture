<?php

/**
 * Created by PhpStorm.
 * User: mariozuany
 * Date: 27/08/2016
 * Time: 21:00 h
 */

namespace Drupal\api_produto_hab\Controller;

use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\api_produto_hab\Service\ModelosService;
use Drupal\api_produto_hab\Service\FeaturesService;

class WsCompleteAPIController extends ControllerBase
{
  public function call(Request $request)
  {
    $modelos = new ModelosService();
    $responseModelos = $modelos->sendRequest();
    
    $features = new FeaturesService();
    $responseFeatures = $features->sendRequest();

    $result = array_merge_recursive($responseModelos, $responseFeatures);

    return new JsonResponse($result);
  }
}