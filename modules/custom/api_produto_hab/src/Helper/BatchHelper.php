<?php

namespace Drupal\api_produto_hab\Helper;

use Drupal\api_produto_hab\Model\VeiculoModel;
use Drupal\api_produto_hab\Model\ColorsModel;
use Drupal\api_produto_hab\Helper\DeleteHelper;

/**
 * Class BatchHelper
 *
 * @package Drupal\api_produto_hab\Helper
 */
class BatchHelper
{
  /**
   * @param array $row
   * @param array $first_row
   * @param array $context
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function handle($row, $first_row, $type, &$context)
  {
    if($type == 'veiculo'){
      $veiculoModel = new VeiculoModel();
      $veiculoModel->createNode($row,$first_row);
    }
    else{
      $colorsModel = new ColorsModel();
      $colorsModel->createNode($row,$first_row);
    }
    $context['results'][] = $row;
  }

  /**
   * @param int $success
   * @param array $results
   * @param array $operations
   */
  function handleFinishedCallback($success, $results, $operations)
  {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(count($results), 'One node processed.', 'Total of @count nodes processed.');
    } else {
      $message = t('Finished with an error.');
    }

    drupal_set_message($message);
  }

  /**
   * @param array $ids
   * @param array $context
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function handleDelete($ids, $entityType, &$context)
  {
    $Model = new DeleteHelper();
    $Model->deleteEntities($ids, $entityType);

    $context['results'][] = $ids;
  }

  /**
   * @param int $success
   * @param array $results
   * @param array $operations
   */
  function handleFinishedDeleteCallback($success, $results, $operations)
  {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(count($results), 'One node processed.', 'Total of @count nodes processed.');
    } else {
      $message = t('Finished with an error.');
    }

    drupal_set_message($message);
  }
}
