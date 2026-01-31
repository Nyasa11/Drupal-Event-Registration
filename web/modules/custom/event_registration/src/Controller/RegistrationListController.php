<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for viewing and managing event registrations.
 */
class RegistrationListController extends ControllerBase {

  /**
   * Display registration listing page with filters.
   */
  public function listRegistrations() {
    // Build the filter form
    $form = \Drupal::formBuilder()->getForm('Drupal\event_registration\Form\RegistrationFilterForm');

    return [
      'form' => $form,
      'table' => [
        '#markup' => '<div id="registrations-table"></div>',
      ],
    ];
  }

}