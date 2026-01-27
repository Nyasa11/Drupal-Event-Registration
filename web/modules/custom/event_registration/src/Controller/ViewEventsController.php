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

}