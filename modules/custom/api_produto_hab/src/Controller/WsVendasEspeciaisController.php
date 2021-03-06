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
use Drupal\api_produto_hab\Service\PrecosEspeciaisService;

class WsVendasEspeciaisController extends ControllerBase
{
  public function call()
  {
    $modelos = new ModelosService();
    $responseModelos = $modelos->sendRequest();
    
    $vendas_diretas = new PrecosEspeciaisService();
    $responseVendasDiretas = $vendas_diretas->sendRequest();

    $result = array_merge_recursive($responseModelos, $responseVendasDiretas);

    return new JsonResponse(array_values($result));
  }
}
