<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Event Registration Form for users.
 */
class EventRegistrationForm extends FormBase {

  /**
   * The email service.
   *
   * @var \Drupal\event_registration\Service\EmailService
   */
  protected $emailService;

  /**
   * {@inheritdoc}
   */
  public static function create(\Symfony\Component\DependencyInjection\ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->emailService = $container->get('event_registration.email_service');
    return $instance;
  }
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'event_registration_user_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['full_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Full Name'),
      '#required' => TRUE,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email Address'),
      '#required' => TRUE,
    ];

    $form['college_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('College Name'),
      '#required' => TRUE,
    ];

    $form['department'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Department'),
      '#required' => TRUE,
    ];

    // Temporary hardcoded dropdown - will make dynamic with AJAX later
    $form['event_category'] = [
      '#type' => 'select',
      '#title' => $this->t('Category of Event'),
      '#options' => [
        '' => $this->t('- Select Category -'),
        'online_workshop' => $this->t('Online Workshop'),
        'hackathon' => $this->t('Hackathon'),
        'conference' => $this->t('Conference'),
        'oneday_workshop' => $this->t('One-day Workshop'),
      ],
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::updateDatesCallback',
        'wrapper' => 'event-date-wrapper',
        'event' => 'change',
      ],
    ];

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => $this->getDateOptions($form_state),
      '#required' => TRUE,
      '#prefix' => '<div id="event-date-wrapper">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::updateEventNamesCallback',
        'wrapper' => 'event-name-wrapper',
        'event' => 'change',
      ],
    ];

    $form['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => $this->getEventNameOptions($form_state),
      '#required' => TRUE,
      '#prefix' => '<div id="event-name-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Register for Event'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $full_name = $form_state->getValue('full_name');
    $college_name = $form_state->getValue('college_name');
    $department = $form_state->getValue('department');
    $email = $form_state->getValue('email');

    // Check for special characters in Full Name.
    if (!preg_match('/^[a-zA-Z\s]+$/', $full_name)) {
      $form_state->setErrorByName('full_name', 
        $this->t('Full Name should not contain special characters or numbers.')
      );
    }

    // Check for special characters in College Name.
    if (!preg_match('/^[a-zA-Z0-9\s]+$/', $college_name)) {
      $form_state->setErrorByName('college_name', 
        $this->t('College Name should not contain special characters.')
      );
    }

    // Check for special characters in Department.
    if (!preg_match('/^[a-zA-Z\s]+$/', $department)) {
      $form_state->setErrorByName('department', 
        $this->t('Department should not contain special characters or numbers.')
      );
    }

    // Email validation is automatic with #type => 'email',
    // but let's add a custom check to be safe.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $form_state->setErrorByName('email', 
        $this->t('Please enter a valid email address.')
      );
    }
    // Check for duplicate registration (email + event_date).
    $event_date = $form_state->getValue('event_date');

    // Only check if both email and event_date are provided.
    if ($email && $event_date) {
      $query = \Drupal::database()->select('event_registration', 'er')
        ->fields('er', ['id'])
        ->condition('email', $email)
        ->condition('event_id', $event_date)  // We'll fix this later when AJAX is done
        ->execute()
        ->fetchField();

      if ($query) {
        $form_state->setErrorByName('email', 
          $this->t('You have already registered for this event.')
        );
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get form values.
    $full_name = $form_state->getValue('full_name');
    $email = $form_state->getValue('email');
    $college_name = $form_state->getValue('college_name');
    $department = $form_state->getValue('department');
    $event_name = $form_state->getValue('event_name');

    // Temporary: use 1 as event_id until AJAX is implemented.
    $event_id = $event_name ? $event_name : 1;

    // Insert into database.
    try{
    \Drupal::database()->insert('event_registration')
      ->fields([
        'full_name' => $full_name,
        'email' => $email,
        'college_name' => $college_name,
        'department' => $department,
        'event_id' => $event_id,
        'created' => time(),
      ])
      ->execute();
      // Send confirmation email to user
      $this->emailService->sendUserConfirmation([
        'full_name' => $full_name,
        'email' => $email,
        'event_id' => $event_id,
      ]);

    // Personalized success message.
    $this->messenger()->addStatus(
      $this->t('Thank you, @name! Your registration has been submitted successfully.', [
        '@name' => $full_name,
      ])
    );
    }
    catch (\Exception $e) {
      // Show user-friendly error.
      $this->messenger()->addError(
        $this->t('An error occurred. Please try again.')
      );
      
      // Log error for debugging.
      \Drupal::logger('event_registration')->error('Registration save failed: @error', [
        '@error' => $e->getMessage(),
      ]);
    }
  }
  /**
   * Get date options based on selected category.
   */
  protected function getDateOptions(FormStateInterface $form_state) {
    $category = $form_state->getValue('event_category');
    
    $options = ['' => $this->t('- Select Date -')];
    
    if ($category) {
      $query = \Drupal::database()->select('event_config', 'e')
        ->fields('e', ['event_date'])
        ->condition('event_category', $category)
        ->distinct()
        ->orderBy('event_date', 'ASC')
        ->execute();
      
      foreach ($query as $record) {
        $options[$record->event_date] = $record->event_date;
      }
    }
    
    return $options;
  }

  /**
   * Get event name options based on selected category and date.
   */
  protected function getEventNameOptions(FormStateInterface $form_state) {
    $category = $form_state->getValue('event_category');
    $date = $form_state->getValue('event_date');
    
    $options = ['' => $this->t('- Select Event -')];
    
    if ($category && $date) {
      $query = \Drupal::database()->select('event_config', 'e')
        ->fields('e', ['id', 'event_name'])
        ->condition('event_category', $category)
        ->condition('event_date', $date)
        ->execute();
      
      foreach ($query as $record) {
        $options[$record->id] = $record->event_name;
      }
    }
    
    return $options;
  }

  /**
   * AJAX callback for date dropdown.
   */
  public function updateDatesCallback(array &$form, FormStateInterface $form_state) {
    return $form['event_date'];
  }

  /**
   * AJAX callback for event name dropdown.
   */
  public function updateEventNamesCallback(array &$form, FormStateInterface $form_state) {
    return $form['event_name'];
  }
}