<?php

namespace Drupal\serempre;

use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Repository for database-related helper methods.
 *
 * This repository is a service named 'serempre.repository'. You can see
 * how the service is defined in serempre/serempre.services.yml.
 *
 * @ingroup serempre
 */
class SerempreRepository {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Construct a repository object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   *   The translation service.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(Connection $connection, TranslationInterface $translation, MessengerInterface $messenger) {
    $this->connection = $connection;
    $this->setStringTranslation($translation);
    $this->setMessenger($messenger);
  }

  /**
   * Save an entry in the database.
   *
   * Exception handling is shown in this example. It could be simplified
   * without the try/catch blocks, but since an insert will throw an exception
   * and terminate your application if the exception is not handled, it is best
   * to employ try/catch.
   *
   * @param array $entry
   *   An array containing all the fields of the database record.
   *
   * @return int
   *   The number of updated rows.
   *
   * @throws \Exception
   *   When the database insert fails.
   */
  public function insertarUsuario(array $entry) {
    try {
      $return_value = $this->connection->insert('myusers')
        ->fields($entry)
        ->execute();
    }

    catch (\Exception $e) {
      $this->messenger()->addMessage($this->t('Ingreso fallido. Mensaje = %message', [
        '%message' => $e->getMessage(),
      ]), 'error');
    }

    return $return_value ?? NULL;
  }

  /**
   * Get users from table myusers.
   *
   * @return object
   *   An object containing the loaded entries if found.
   *
   * @see Drupal\Core\Database\Connection::select()
   */
  public function cargarUsuarios() {
    // Read all the fields from the myusers table.
    $select = $this->connection
      ->select('myusers')
      // Add all the fields into our select query.
      ->fields('myusers')
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(100);
      
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  /**
   * Get all the users from table myusers.
   *
   * @return object
   *   An object containing the loaded entries if found.
   *
   * @see Drupal\Core\Database\Connection::select()
   */
  public function cargarTodosUsuarios() {
    // Read all the fields from the myusers table.
    $select = $this->connection
      ->select('myusers')
      // Add all the fields into our select query.
      ->fields('myusers');
      
    // Return the result in object format.
    return $select->execute()->fetchAll();
  }

  /**
   * Get users from database without the ID.
   *
   * @return object
   *   An object containing the loaded entries if found.
   *
   * @see Drupal\Core\Database\Connection::select()
   */
  public function cargarUsuariosConID() {
    // Read all the fields from the myusers table.
    $select = $this->connection
      ->select('myusers')
      // Add all the fields into our select query.
      ->fields('myusers');

    // Return the result in an array.
    return $select->execute()->fetchAllKeyed();
  }
}
