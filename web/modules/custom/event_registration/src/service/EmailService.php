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
    // We'll implement this in next commit
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