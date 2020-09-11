<?php

namespace Drupal\serempre\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\serempre\SerempreRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 * Form to export users from table myusers.
 * 
 * * @ingroup serempre
 */
class SerempreExportarForm implements FormInterface, ContainerInjectionInterface {

  use StringTranslationTrait;
  use MessengerTrait;

  /**
   * This repository is used for our specialized queries.
   *
   * @var \Drupal\serempre\SerempreRepository
   */
  protected $repository;

  /**
   * {@inheritdoc}
   *
   * We'll use the ContainerInjectionInterface pattern here to inject the
   * current user and also get the string_translation service.
   */
  public static function create(ContainerInterface $container) {
    $form = new static(
      $container->get('serempre.repository'),
    );

    $form->setStringTranslation($container->get('string_translation'));
    $form->setMessenger($container->get('messenger'));
    
    return $form;
  }

  /**
   * Construct the new form object.
   */
  public function __construct(SerempreRepository $repository) {
    $this->repository = $repository;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'serempre_exportar_form';
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
      '#markup' => '<h1>Para exportar los usuarios haga click en Exportar</h1>',
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Exportar'),
    ];

    return $form;
  }

  /**
   * Function to validate the content on the server side.
   * 
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * Function to submit the form if ajax is not used.
   * 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Start using PHP's built in file handler functions to create a temporary file.
    $handle = fopen('php://temp', 'w+');

    $headers = [
      $this->t('ID'),
      $this->t('Nombre'),
    ];

    // Add the header as the first line of the CSV.
    fputcsv($handle, $headers);

    // Get all the users from the repo.
    $content = $this->repository->cargarTodosUsuarios();

    foreach ($content as $entry) {
      $data = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);

      // Add the data we exported to the next line of the CSV>
      fputcsv($handle, array_values($data));
    }

    // Reset where we are in the CSV.
    rewind($handle);

    // Retrieve the data from the file handler.
    $csv_data = stream_get_contents($handle);

    // Close the file handler since we don't need it anymore.  We are not storing this file anywhere in the filesystem.
    fclose($handle);

    // This is the "magic" part of the code. Once the data is built, we can return it as a response.
    $response = new Response();

    // By setting these 2 header options, the browser will see the URL// used by this Controller to return a CSV file called "myusers.csv".
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="myusers.csv"');

    // This line physically adds the CSV data we created 
    $response->setContent($csv_data);

    // Set the response object in the form state.
    $form_state->setResponse($response);
  }
}
