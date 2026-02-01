<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for viewing and managing event registrations.
 */
class RegistrationListController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  /**
   * Display registration listing page with filters.
   */
  public function listRegistrations() {
    // Build the filter form
    $form = $this->formBuilder->getForm('Drupal\event_registration\Form\RegistrationFilterForm');

    return [
      'form' => $form,
      'table' => [
        '#markup' => '<div id="registrations-table"></div>',
      ],
    ];
  }

}