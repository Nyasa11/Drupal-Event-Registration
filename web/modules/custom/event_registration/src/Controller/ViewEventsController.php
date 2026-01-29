<?php

namespace Drupal\event_registration\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller to view saved events.
 */
class ViewEventsController extends ControllerBase {

  /**
   * Display all saved events.
   */
  public function viewEvents() {
    $query = \Drupal::database()->select('event_config', 'e')
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
  /*
   * Test if registration table exists.
   */
  public function testRegistrationTable() {
    $table_exists = \Drupal::database()->schema()->tableExists('event_registration');
    
    $message = $table_exists 
      ? '✅ event_registration table EXISTS' 
      : '❌ event_registration table NOT FOUND';

    return [
      '#markup' => '<h2>Database Check</h2><p>' . $message . '</p>',
    ];
  }
  /**
   * Display all user registrations.
   */
  public function viewRegistrations() {
    $query = \Drupal::database()->select('event_registration', 'er')
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