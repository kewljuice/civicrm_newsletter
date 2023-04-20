<?php

namespace Drupal\civicrm_newsletter\Form;

use Drupal\civicrm_newsletter\Utility\NewsletterInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterAjaxSubscribeName.
 */
class NewsletterAjaxSubscribeName extends FormBase {

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
    return 'civicrm_newsletter_ajax_form_subscribe_name_email';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // Form components.
    $form['first_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => ['placeholder' => $this->t('First name')],
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#required' => TRUE,
      '#attributes' => ['placeholder' => $this->t('Last name')],
    ];

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
      '#type'  => 'submit',
      '#value' => $this->t('Subscribe'),
      '#ajax'  => [
        'callback' => '::ajaxSubmitForm',
      ],
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
  }

  /**
   * Callback for submission.
   *
   * Subscribe the contact and replace the form with a message.
   *
   * @return mixed
   *   The AJAX response.
   */
  public function ajaxSubmitForm(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // Groups.
    $group = $this->config('civicrm_newsletter.settings')->get('default');

    // Parameters.
    $firstName = $form_state->getValue('first_name');
    $lastName = $form_state->getValue('last_name');
    $email = $form_state->getValue('email');

    // Important the that key equals the CiviCRM field key.
    $params = [
      'first_name' => $firstName,
      'last_name' => $lastName,
      'email' => $email,
    ];

    // Create.
    $this->newsletter->createSubscription($params, $group);

    // Display the results.
    $div = '<div class="civicrm-newsletter-confirmation-message"><p>' . $this->t('The subscription has been submitted.') . '</p></div>';
    $response->addCommand(new ReplaceCommand('#civicrm-newsletter-ajax-form-subscribe-name-email', $div));

    return $response;
  }

}
