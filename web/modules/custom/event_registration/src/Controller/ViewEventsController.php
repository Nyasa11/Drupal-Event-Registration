<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to view saved events.
 */
class ViewEventsController extends ControllerBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * Display all saved events.
   */
  public function viewEvents() {
    $query = $this->database->select('event_config', 'e')
      ->fields('e')
      ->execute();

    $rows = [];
    foreach ($query as $record) {
      $rows[] = [
        $record->id,
        $record->event_name,
        $record->event_category,
        $record->event_date,
        $record->registration_start_date,
        $record->registration_end_date,
        date('Y-m-d H:i:s', $record->created),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => ['ID', 'Event Name', 'Category', 'Event Date', 'Reg Start', 'Reg End', 'Created'],
      '#rows' => $rows,
      '#empty' => $this->t('No events found.'),
    ];
  }

  /**
   * Display all user registrations.
   */
  public function viewRegistrations() {
    $query = $this->database->select('event_registration', 'er')
      ->fields('er')
      ->execute();

    $rows = [];
    foreach ($query as $record) {
      $rows[] = [
        $record->id,
        $record->full_name,
        $record->email,
        $record->college_name,
        $record->department,
        $record->event_id,
        date('Y-m-d H:i:s', $record->created),
      ];
    }

    return [
      '#theme' => 'table',
      '#header' => ['ID', 'Name', 'Email', 'College', 'Department', 'Event ID', 'Created'],
      '#rows' => $rows,
      '#empty' => $this->t('No registrations found.'),
    ];
  }
}