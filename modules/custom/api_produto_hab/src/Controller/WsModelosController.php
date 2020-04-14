<?php

/**
 * Created by PhpStorm.
 * User: mariozuany
 * Date: 27/08/2016
 * Time: 21:00 h
 */

namespace Drupal\api_produto_hab\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\api_produto_hab\Service\ModelosService;

class WsModelosController extends ControllerBase
{
  public function call()
  {
    $modelos = new ModelosService();
    $response = $modelos->sendRequest();
    
    return new JsonResponse($response);
  }
}
