<?php

namespace Drupal\api_produto_hab\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\api_produto_hab\Helper\SpreadsheetHelper;
use Drupal\api_produto_hab\Helper\DeleteHelper;

/**
 * Class ImportForm.
 *
 * @package Drupal\api_produto_hab\Form
 */
class ImportForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
  public function getFormId()
  {
    return 'import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $form['#attributes'] = ['enctype' => 'multipart/form-data'];

    $form['file'] = [
      '#type'             => 'file',
      '#title'            => t('File'),
      '#description'      => ($max_size = file_upload_max_size()) ? t('Extensões permitidas: xlsx. <br> Tamanho máximo de upload é: @max_size</strong>.', ['@max_size' => format_size($max_size)]) : '',
      '#element_validate' => ['::validateFileUpload'],
    ];

    $form['actions'] = array('#type' => 'actions');

    $form['actions']['submit'] = [
      '#type'  => 'submit',
      '#value' => $this->t('Submit'),
    ];
    
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateFileUpload(array &$form, FormStateInterface $form_state)
  {    
    // TODO: Refactor this function
    if ($file = file_save_upload('file', ['file_validate_extensions' => ['xlsx XLSX']], FALSE, 0, FILE_EXISTS_REPLACE)) {
      $destination = 'public://api_produto_hab/' . $file->getFilename();

      if (file_copy($file, $destination, FILE_EXISTS_REPLACE)) {
        $form_state->setValue('file_uploaded', $destination);
      } else {
        $form_state->setErrorByName('file', t('Impossível copiar o arquivo para @destination', ['@destination' => $destination]));
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state)
  {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $spreadsheetHelper= new SpreadsheetHelper();
    
    $file = \Drupal::service('file_system')->realpath($form_state->getValue('file_uploaded'));
    
    $return = $spreadsheetHelper->readFile($file,true);
    $batch = array(
      'title'            => t('Update...'),
      'operations'       => [],
      'init_message'     => t('Commencing...'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished'         => '\Drupal\api_produto_hab\Helper\BatchHelper::handleFinishedCallback',
    );
    
    foreach ($return['rows'] as $key => $row) {
      $batch['operations'][] = ['\Drupal\api_produto_hab\Helper\BatchHelper::handle', [$row, $return['header']]];
    }

    (new DeleteHelper())->cleanHistory();
    batch_set($batch);
  }
}
