<?php

namespace Drupal\civicrm_newsletter\Form;

use Drupal\civicrm_newsletter\Utility\NewsletterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterSubscribe.
 */
class NewsletterSubscribe extends FormBase {

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The Newsletter service.
   *
   * @var \Drupal\civicrm_newsletter\Utility\NewsLetterInterface
   */
  protected $newsletter;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('civicrm_newsletter.list')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\civicrm_newsletter\Utility\NewsLetterInterface $newsletter
   *   The Newsletter service.
   */
  public function __construct(MessengerInterface $messenger, NewsLetterInterface $newsletter) {
    $this->messenger = $messenger;
    $this->newsletter = $newsletter;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civicrm_newsletter_form_subscribe';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Form components.
    $form['email'] = [
      '#type' => 'email',
      '#description' => $this->t('Please enter email address, read and accept the terms of use of the site.'),
      '#required' => TRUE,
      '#attributes' => ['placeholder' => $this->t('Email')],
    ];

    $form['accept'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('I accept the terms of use of the site'),
    ];
    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $accept = $form_state->getValue('accept');
    if (empty($accept)) {
      // Set an error for the form element with a key of "accept".
      $form_state->setErrorByName('accept', $this->t('You must accept the terms of use to continue'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Parameters.
    $email = $form_state->getValue('email');
    $group = $this->config('civicrm_newsletter.settings')->get('default');
    // Create.
    $this->newsletter->createSubscription($email, $group);
    // Display the results.
    $this->messenger->addMessage($this->t('The subscription has been submitted.'));
  }

}
