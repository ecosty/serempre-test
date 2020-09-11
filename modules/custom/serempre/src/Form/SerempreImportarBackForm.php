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
 * Form to import users to table myusers using csv file already uploaded.
 * 
 * * @ingroup serempre
 */
class SerempreImportarBackForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'serempre_importar_back_form';
  }
  
  /**
   * Function that builds the form.
   * 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'box-container'],
    ];

    $form['container']['message'] = [
      '#type' => 'markup',
      '#markup' => '<h1>Para importar usuarios en backend haga click en Importar</h1>',
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Importar'),
    ];

    return $form;
  }

  /**
   * Function to submit the form if ajax is not used.
   * 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Execute a background process to call a drush command <<drush ium>> (Async process).
    exec("drush ium" . " > /dev/null &");

    // Asyc Message.
    $this->messenger()->addMessage("La importación se encuentra corriendo en background y estará lista dentro de unos minutos.");
  }
}
