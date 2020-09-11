<?php

namespace Drupal\serempre\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\serempre\SerempreRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Serempre Test.
 *
 * @ingroup serempre
 */
class SerempreController extends ControllerBase {

  /**
   * This repository is used for our specialized queries.
   *
   * @var \Drupal\serempre\SerempreRepository
   */
  protected $repository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $controller = new static($container->get('serempre.repository'));
    $controller->setStringTranslation($container->get('string_translation'));
    return $controller;
  }

  /**
   * Construct a new controller.
   *
   * @param \Drupal\serempre\SerempreRepository $repository
   */
  public function __construct(SerempreRepository $repository) {
    $this->repository = $repository;
  }

  /**
   * Render a list of entries in the database.
   */
  public function usuarios() {
    $content = [];

    $content['mensaje'] = [
      '#markup' => $this->t('Genera una lista de todos los usuarios guardados en la base myusers.'),
    ];

    $rows = [];
    $headers = [
      $this->t('ID'),
      $this->t('Nombre'),
    ];

    $entries = $this->repository->cargarUsuarios();

    foreach ($entries as $entry) {
      $rows[] = array_map('Drupal\Component\Utility\Html::escape', (array) $entry);
    }

    $content['table'] = [
      '#type' => 'table',
      '#header' => $headers,
      '#rows' => $rows,
      '#empty' => $this->t('No existen usuarios registrados.'),
    ];

    // Finally add the pager.
    $content['pager'] = array(
      '#type' => 'pager'
    );

    // Don't cache this page.
    $content['#cache']['max-age'] = 0;

    return $content;
  }
}
