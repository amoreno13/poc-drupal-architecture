<?php

namespace Drupal\api_produto_hab\Helper;

class DeleteHelper
{
  private $pageSize;

  public function __construct() {
    $this->pageSize = 1000;
  }

  public function cleanHistory() {

    $entities = $this->getEntities();

    $batch = $this->getBatchDelete();

    foreach($entities as $entity){
      $ids = \Drupal::entityQuery($entity['type'])->condition($entity['id'], $entity['name'])->execute();

      $this->configureBatch($entity['type'], $ids, $batch);
    }

    batch_set($batch);
  }

  public function deleteEntities($ids, $entityType) {

    $storageHandler = \Drupal::entityTypeManager()->getStorage($entityType);

    $entities = $storageHandler->loadMultiple($ids);
    $storageHandler->delete($entities);
  }

  private function getEntities() {
    return [
      ['type' => 'node', 'id' => 'type', 'name' => 'veiculo'],
    ];
  }

  private function getBatchDelete() {
    return [      
      'title'            => t('Update Revisions...'),
      'operations'       => [],
      'init_message'     => t('Commencing...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished'         => '\Drupal\api_produto_hab\Helper\BatchHelper::handleFinishedDeleteCallback',    
    ];
  }

  private function configureBatch($entityType, $ids, &$batch) {

    if(count($ids) > $this->pageSize){
      $this->pagedIds($entityType, $ids, $batch);
    }else{
      $this->setBatchOperation($entityType, $ids, $batch);
    }    
  }

  private function pagedIds($entityType, $ids, &$batch) {

    for($i = 0; $i <= count($ids); $i = $i + $this->pageSize){
      $pagesIds = array_slice($ids, $i, $this->pageSize);
      $this->setBatchOperation($entityType, $pagesIds, $batch);
    }
  }

  private function setBatchOperation($entityType, $ids, &$batch) { 
    $batch['operations'][] = ['\Drupal\api_produto_hab\Helper\BatchHelper::handleDelete', [$ids, $entityType]];
  }
}
