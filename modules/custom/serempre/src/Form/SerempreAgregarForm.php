<?php

namespace Drupal\serempre\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\serempre\SerempreRepository;

/**
 * Form to add a new user in table myusers
 *
 * @ingroup serempre
 */
class SerempreAgregarForm implements FormInterface, ContainerInjectionInterface {

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
    return 'serempre_agregar_form';
  }

  /**
   * Function that builds the form.
   * 
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Add JS library to validate the user name.
    $form['#attached']['library'][] = 'serempre/serempre.library';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    $form['agregar'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'agregar-container',
      ],
    ];

    $form['agregar']['box'] = [
      '#type' => 'markup',
      '#markup' => '<h1>Ingrese el nombre de usuario (Minimo 5 caracteres).</h1>',
    ];

    $form['agregar']['nombre'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre'),
      '#size' => 20,
      '#attributes' => [
        'name' => "nombre"
      ],
    ];

    $form['agregar']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Agregar'),
      '#id' => 'submit-agregar-button',
      '#ajax' => [
        'callback' => '::agregarUsuario',
        'wrapper' => 'agregar-container',
      ],
    ];

    $form['agregar']['message'] = [
      '#type' => 'markup',
      '#markup' => '',
    ];

    return $form;
  }

  /**
   * Function to validate the content on the server side.
   * 
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $new_user = $form_state->getValue('nombre');
    
    // Confirm that username has at least 5 characters.
    if (strlen($new_user) < 5) {
      $form_state->setErrorByName('nombre', $this->t('El nombre debe ser mayor a 5 caracteres.'));
    }

    // Confirm the value is not duplicated.
    $current_users = $this->repository->cargarUsuariosConID();
    foreach ($current_users as $key => $user) {
      if($new_user == $user) {
        $form_state->setErrorByName('nombre', $this->t('Este usuario ya se encuentra registrado.'));
      }
    }
  }

  /**
   * Function to submit the form if ajax is not used.
   * 
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Function to submit the form by ajax.
   * 
   * {@inheritdoc}
   */
  public function agregarUsuario(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    if ($form_state->getErrors()) {
      $form['agregar'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];

      $response->addCommand(new OpenModalDialogCommand($this->t('Error al agregar usuario'), $form, static::getDataDialogOptions()));
    }
    else {
      $this->messenger()->deleteAll();

      $entry = [
        'nombre' => $form_state->getValue('nombre')
      ];

      $return = $this->repository->insertarUsuario($entry);
        
      if ($return) {
        $message = t('<h1>El usuario @nombre ha sido creado exitosamente con el codigo @id.</h1>', ['@nombre' => $entry['nombre'], '@id' => $return]);
      }
      else {
        $message = t('No se pudo crear el usuario debido a un error en el sistema.');
      }  
            
      $content = [
        '#type' => 'item',
        '#markup' => $message,
      ];
      
      $response->addCommand(new OpenModalDialogCommand($this->t('Usuario agregado exitosamente'), $content, static::getDataDialogOptions()));
    }

    return $response;
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '50%',
    ];
  }
}
