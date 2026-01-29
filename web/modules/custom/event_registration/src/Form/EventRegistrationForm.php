<?php

namespace Drupal\event_registration\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Event Registration Form for users.
 */
class EventRegistrationForm extends FormBase {

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
    ];

    $form['event_date'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Date'),
      '#options' => [
        '' => $this->t('- Select Date -'),
      ],
      '#required' => FALSE,
    ];

    $form['event_name'] = [
      '#type' => 'select',
      '#title' => $this->t('Event Name'),
      '#options' => [
        '' => $this->t('- Select Event -'),
      ],
      '#required' => False , 
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
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->messenger()->addMessage($this->t('Registration submitted (not saved yet).'));
  }

}