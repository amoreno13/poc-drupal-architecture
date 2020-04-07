<?php

/**
 * Created by PhpStorm.
 * User: mariozuany
 * Date: 27/08/2016
 * Time: 21:00 h
 */

namespace Drupal\ws_api_produto\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\ws_api_produto\Service\ModelosService;

class WsModelosController extends ControllerBase
{
  public function call()
  {
    $modelos = new ModelosService();
    $response = $modelos->sendRequest();
    
    return new JsonResponse($response);
  }
}
