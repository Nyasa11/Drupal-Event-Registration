<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
/**
 * Filter form for registration listings.
 */

class RegistrationFilterForm extends FormBase {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->loggerFactory = $container->get('logger.factory');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_filter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Event Date filter
    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $this->getDateOptions(),
      '#empty_option' => $this->t('- All Dates -'),
      '#ajax' => [
        'callback' => '::updateEventNamesCallback',
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    // Event Name filter (updates based on date)
    $form['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $this->getEventNameOptions($form_state),
      '#empty_option' => $this->t('- All Events -'),
      '#prefix' => '<div id="event-name-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateTableCallback',
        'wrapper' => 'registrations-table-wrapper',
        'event' => 'change',
      ],
    ];

    // Registrations table
    $form['registrations_table'] = [
      '#type' => 'container',
      '#prefix' => '<div id="registrations-table-wrapper">',
      '#suffix' => '</div>',
    ];

    // Get filtered registrations
    $registrations = $this->getFilteredRegistrations($form_state);
    
    // Display total count
    $form['registrations_table']['count'] = [
      '#markup' => '<h3>' . $this->t('Total Participants: @count', [
        '@count' => count($registrations)]) . '</h3>',
    ];

    // Build table
    $form['registrations_table']['table'] = [
      '#theme' => 'table',
      '#header' => [
        $this->t('Name'),
        $this->t('Email'),
        $this->t('Event Date'),
        $this->t('College Name'),
        $this->t('Department'),
        $this->t('Submission Date'),
      ],
      '#rows' => $registrations,
      '#empty' => $this->t('No registrations found.'),
    ];

    // Export to CSV button
    $form['export'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export to CSV'),
      '#submit' => ['::exportCsv'],
    ];

    return $form;
  }

  /**
   * Get all event dates.
   */
  protected function getDateOptions() {
    $options = [];
    
    $query = $this->database->select('event_config', 'e')
      ->fields('e', ['event_date'])
      ->distinct()
      ->orderBy('event_date', 'DESC')
      ->execute();
    
    foreach ($query as $record) {
      $options[$record->event_date] = $record->event_date;
    }
    
    return $options;
  }

  /**
   * Get event names based on selected date.
   */
  protected function getEventNameOptions(FormStateInterface $form_state) {
    $options = [];
    $date = $form_state->getValue('event_date');
    
    if ($date) {
      $query = $this->database->select('event_config', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_date', $date)
        ->execute();
      
      foreach ($query as $record) {
        $options[$record->id] = $record->event_name;
      }
    }
    
    return $options;
  }

  /**
   * Get filtered registrations.
   */
  protected function getFilteredRegistrations(FormStateInterface $form_state) {
    $rows = [];
    
    $query = $this->database->select('event_registration', 'er');
    $query->leftJoin('event_config', 'ec', 'er.event_id = ec.id');
    $query->fields('er', ['full_name', 'email', 'college_name', 'department', 'created']);
    $query->fields('ec', ['event_date']);
    
    if ($date = $form_state->getValue('event_date')) {
      $query->condition('ec.event_date', $date);
    }

    if ($event_id = $form_state->getValue('event_name')) {
      $query->condition('er.event_id', $event_id);
    }
    
    $query->orderBy('er.created', 'DESC');
    $results = $query->execute();
    
    foreach ($results as $record) {
      $rows[] = [
        $record->full_name,
        $record->email,
        $record->event_date ?? 'N/A',
        $record->college_name,
        $record->department,
        date('Y-m-d H:i:s', $record->created),
      ];
    }
    
    return $rows;
  }

  /**
   * AJAX callback for event name dropdown.
   */
  public function updateEventNamesCallback(array &$form, FormStateInterface $form_state) {
    return $form['event_name'];
  }

  /**
   * AJAX callback for table update.
   */
  public function updateTableCallback(array &$form, FormStateInterface $form_state) {
    return $form['registrations_table'];
  }

  /**
   * Export registrations to CSV.
   */
  public function exportCsv(array &$form, FormStateInterface $form_state) {
    $database = $this->database;
    
    // Get filtered registrations
    $date = $form_state->getValue('event_date');
    $event_id = $form_state->getValue('event_name');
    
    $query = $database->select('event_registration', 'er');
    $query->leftJoin('event_config', 'ec', 'er.event_id = ec.id');
    
    // Select fields explicitly with aliases
    $query->addField('er', 'full_name');
    $query->addField('er', 'email');
    $query->addField('er', 'college_name');
    $query->addField('er', 'department');
    $query->addField('er', 'created');
    $query->addField('ec', 'event_name', 'event_name');
    $query->addField('ec', 'event_date', 'event_date');
    $query->addField('ec', 'event_category', 'event_category');
    
    // Apply filters
    if ($date) {
      $query->condition('ec.event_date', $date);
    }
    
    if ($event_id) {
      $query->condition('er.event_id', $event_id);
    }
    
    $query->orderBy('er.created', 'DESC');
    
    try {
      $results = $query->execute()->fetchAll();
      
      // Create CSV content
      $csv_data = [];
      $csv_data[] = [
        'Name',
        'Email',
        'College Name',
        'Department',
        'Event Name',
        'Event Date',
        'Event Category',
        'Submission Date'
      ];
      
      foreach ($results as $record) {
        $csv_data[] = [
          $record->full_name ?? '',
          $record->email ?? '',
          $record->college_name ?? '',
          $record->department ?? '',
          $record->event_name ?? 'N/A',
          $record->event_date ?? 'N/A',
          $record->event_category ?? 'N/A',
          !empty($record->created) ? date('Y-m-d H:i:s', $record->created) : 'N/A',
        ];
      }
      
      // Generate CSV file
      $filename = 'event_registrations_' . date('Y-m-d_His') . '.csv';
      
      $handle = fopen('php://temp', 'r+');
      foreach ($csv_data as $row) {
        fputcsv($handle, $row);
      }
      rewind($handle);
      $csv_content = stream_get_contents($handle);
      fclose($handle);
      
      // Send CSV response
      $response = new \Symfony\Component\HttpFoundation\Response($csv_content);
      $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
      $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
      
      $form_state->setResponse($response);
      
    } catch (\Exception $e) {
      $this->loggerFactory->get('event_registration')->error('CSV export failed: @error', [
        '@error' => $e->getMessage(),
      ]);
      $this->messenger()->addError($this->t('Failed to export CSV. Please check the logs.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Not needed for this form
  }

}