<?php

/**
 * @file
 * Contains \Drupal\serempre\Form\SerempreImportarForm.
 */

namespace Drupal\serempre\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Form to import users from a csv file to table myusers.
 * 
 * * @ingroup serempre
 */
class SerempreImportarForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'serempre_importar_form';
  }
  
  /**
   * Function that builds the form.
   * 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['description'] = array(
      '#markup' => '<p>Importar usuarios desde un archivo CSV</p>',
    );

    $form['import_csv'] = array(
      '#type' => 'managed_file',
      '#title' => t('Agregar el archivo aqui'),
      '#upload_location' => 'public://importcsv/',
      '#default_value' => '',
      "#upload_validators"  => array(
        "file_validate_extensions" => array("csv")
      ),
      '#states' => array(
        'visible' => array(
          ':input[name="File_type"]' => array('value' => t('Upload Your File')),
        ),
      ),
    );

    $form['actions']['#type'] = 'actions';

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Importar'),
      '#button_type' => 'primary',
    );

    return $form;
  }

  /**
   * Function to submit the form if ajax is not used.
   * 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /* Fetch the array of the file stored temporarily in database */
    $csv_file = $form_state->getValue('import_csv');
    
    /* Load the object of the file by it's fid */
    $file = File::load( $csv_file[0] );

    /* Set the status flag permanent of the file object */
    $file->setPermanent();

    /* Save the file in database */
    $file->save();

    // You can use any sort of function to process your data. The goal is to get each 'row' of data into an array
    // If you need to work on how data is extracted, process it here.
    $data = $this->csvtoarray($file->getFileUri(), ',');

    // Set a number limit to manaje the batches.
    $num_chunks = 100;

    // Separate array in chunks of $num_chunks size.
    $chunks = array_chunk($data, $num_chunks);

    // Create the batch sections for each chunk.
    foreach ($chunks as $key => $chunk) {
      foreach ($chunk as $key => $row) {
        // Operations for each row (function defined in .module file).
        $operations[] = [
          'serempre_batch_agregar_usuario',
          [
            $row
          ]
        ];  
      }

      $batch = array(
        'title' => t('Importing Data...'),
        'operations' => $operations,
        'init_message' => t('Import is starting.'),
        'finished' => 'serempre_batch_agregar_usuario_finished',
      );

      batch_set($batch);
    }
  }

  /**
   * Internal function to convert a csv file to array.
   */
  public function csvtoarray($filename='', $delimiter) {
    if(!file_exists($filename) || !is_readable($filename)) {
      return FALSE;
    }
      
    $header = NULL;
    $data = array();

    if (($handle = fopen($filename, 'r')) !== FALSE ) {
      while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
        if(!$header) {
          $header = $row;
        }
        else {
          $data[] = array_combine($header, $row);
        }
      }

      fclose($handle);
    }

    return $data;
  }
}
