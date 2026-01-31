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
  public function __construct(
    MailManagerInterface $mail_manager,
    ConfigFactoryInterface $config_factory,
    Connection $database
  ) {
    $this->mailManager = $mail_manager;
    $this->configFactory = $config_factory;
    $this->database = $database;
  }

  /**
   * Send registration confirmation email to user.
   */
  public function sendUserConfirmation(array $registration_data) {
    $to = $registration_data['email'];

    // Get event details.
    $event = $this->database->select('event_config', 'e')
      ->fields('e', ['event_name', 'event_date', 'event_category'])
      ->condition('id', $registration_data['event_id'])
      ->execute()
      ->fetchObject();

    // Build email message.
    $message = t(
      "Dear @name,\n\nThank you for registering for our event!\n\nEvent Details:\n- Event: @event_name\n- Category: @category\n- Date: @date\n\nWe look forward to seeing you!\n\nBest regards,\nEvent Team",
      [
        '@name' => $registration_data['full_name'],
        '@event_name' => $event->event_name ?? 'N/A',
        '@category' => $event->event_category ?? 'N/A',
        '@date' => $event->event_date ?? 'N/A',
      ]
    );

    $params = ['message' => $message];

    // IMPORTANT: Do not fail registration if email fails.
    try {
      $this->mailManager->mail(
        'event_registration',
        'user_confirmation',
        $to,
        'en',
        $params
      );
    }
    catch (\Exception $e) {
      \Drupal::logger('event_registration')->error(
        'User email failed: @message',
        ['@message' => $e->getMessage()]
      );
    }
  }

  /**
   * Send notification email to admin.
   */
  public function sendAdminNotification(array $registration_data) {
    $config = $this->configFactory->get('event_registration.settings');
    $admin_email = $config->get('admin_email');
    $notifications_enabled = $config->get('enable_admin_notifications');

    // If admin notifications are disabled, stop here.
    if (!$notifications_enabled || !$admin_email) {
      return;
    }

    // Get event details.
    $event = $this->database->select('event_config', 'e')
      ->fields('e', ['event_name', 'event_date', 'event_category'])
      ->condition('id', $registration_data['event_id'])
      ->execute()
      ->fetchObject();

    // Build email message.
    $message = t(
      "New Event Registration\n\nRegistrant Details:\n- Name: @name\n- Email: @email\n- College: @college\n- Department: @department\n\nEvent Details:\n- Event: @event_name\n- Category: @category\n- Date: @date\n\nRegistration received at: @time",
      [
        '@name' => $registration_data['full_name'],
        '@email' => $registration_data['email'],
        '@college' => $registration_data['college_name'],
        '@department' => $registration_data['department'],
        '@event_name' => $event->event_name ?? 'N/A',
        '@category' => $event->event_category ?? 'N/A',
        '@date' => $event->event_date ?? 'N/A',
        '@time' => date('Y-m-d H:i:s'),
      ]
    );

    $params = ['message' => $message];

    // IMPORTANT: Do not fail registration if email fails.
    try {
      $this->mailManager->mail(
        'event_registration',
        'admin_notification',
        $admin_email,
        'en',
        $params
      );
    }
    catch (\Exception $e) {
      \Drupal::logger('event_registration')->error(
        'Admin email failed: @message',
        ['@message' => $e->getMessage()]
      );
    }
  }

}
