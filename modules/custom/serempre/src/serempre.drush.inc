<?php

/**
 * Implements hook_drush_command().
 */
function serempre_drush_command() {
  $commands['importar-usuarios-myusers'] = [
    'description' => 'Drush function that import uses from csv file to myusers',
    'aliases' => ['ium'],
    'arguments' => [      
    ],
    'examples' => [       
      'drush ium file sample.csv' => 'Import users from sample.csv file.',
    ],
  ];

  return $commands;
}

/**
 * Drush command logic for rebuilding file cache.
 *
 */
function drush_serempre_importar_usuarios_myusers($arg = NULL) {
  $path = drupal_get_path('module', 'serempre') . '/files/data-test.csv';
  $handle = fopen($path, "r");
  $total_lines = 0;

  // We access to the files line by line and then process it.
  if ($handle) {
    // Get the first line for header.
    $name = fgets($handle);
    
    while ($name !== false) {
      $total_lines++;
      $name = fgets($handle);

      // Function that saves the user into the database (Declarated in .module file).
      serempre_insertar_myuser($name);
    }
    
    fclose($handle);
  }

  drush_print(dt("El proceso ha terminado exitosamente, " . $total_lines . "usuarios agregados."));
}
