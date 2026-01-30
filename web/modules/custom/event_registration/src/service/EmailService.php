<?php

namespace Drupal\event_registration\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Email service for event registration notifications.
 */
class EmailService {

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(MailManagerInterface $mail_manager, ConfigFactoryInterface $config_factory, Connection $database) {
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->database = $database;
  }

  /**
   * Send registration confirmation email to user.
   *
   * @param array $registration_data
   *   Registration data array.
   */
  public function sendUserConfirmation(array $registration_data) {
    $to = $registration_data['email'];
    
    // Get event details
    $event = $this->database->select('event_config', 'e')
      ->fields('e', ['event_name', 'event_date', 'event_category'])
      ->condition('id', $registration_data['event_id'])
      ->execute()
      ->fetchObject();

    // Build email message
    $message = t("Dear @name,\n\nThank you for registering for our event!\n\nEvent Details:\n- Event: @event_name\n- Category: @category\n- Date: @date\n\nWe look forward to seeing you!\n\nBest regards,\nEvent Team", [
      '@name' => $registration_data['full_name'],
      '@event_name' => $event->event_name ?? 'N/A',
      '@category' => $event->event_category ?? 'N/A',
      '@date' => $event->event_date ?? 'N/A',
    ]);

    // Send email
    $params = ['message' => $message];
    $this->mailManager->mail('event_registration', 'user_confirmation', $to, 'en', $params);
  }
  /**
   * Send notification email to admin.
   *
   * @param array $registration_data
   *   Registration data array.
   */
  public function sendAdminNotification(array $registration_data) {
    // We'll implement this in next commit
  }

}