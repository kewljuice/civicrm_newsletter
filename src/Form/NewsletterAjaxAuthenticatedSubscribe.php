<?php

namespace Drupal\civicrm_newsletter\Form;

use Drupal\civicrm_newsletter\Utility\NewsletterInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterAuthenticatedSubscribe.
 */
class NewsletterAjaxAuthenticatedSubscribe extends FormBase {

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
   * The Newsletter service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('civicrm_newsletter.list'),
      $container->get('current_user')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\civicrm_newsletter\Utility\NewsLetterInterface $newsletter
   *   The Newsletter service.
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account interface.
   */
  public function __construct(MessengerInterface $messenger, NewsLetterInterface $newsletter, AccountProxyInterface $account) {
    $this->messenger = $messenger;
    $this->newsletter = $newsletter;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civicrm_newsletter_ajax_form_subscribe_authenticated';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $default_values = [
      'first_name' => '',
      'last_name'  => '',
      'email'      => '',
    ];

    $result = $this->newsletter->getContactDetails();
    if (isset($result['values'])) {
      // If 1 contact is returned and its array key is 0.
      if (count($result['values']) === 1 && isset($result['values'][0])) {
        foreach ($default_values as $key => $value) {
          if (isset($result['values'][0][$key])) {
            $default_values[$key] = $result['values'][0][$key];
          }
        }
      }
    }

    // Form components.
    // Only show first_name and last_name if both are filled in on the contact.
    if ($default_values['first_name'] !== '' && $default_values['last_name'] !== '') {
      // The fields should be shown, but also disabled.
      $form['first_name'] = [
        '#type'          => 'textfield',
        '#required'      => TRUE,
        '#disabled'      => TRUE,
        '#attributes'    => ['placeholder' => $this->t('First name')],
        '#default_value' => $default_values['first_name'],
      ];

      $form['last_name'] = [
        '#type'          => 'textfield',
        '#required'      => TRUE,
        '#disabled'      => TRUE,
        '#attributes'    => ['placeholder' => $this->t('Last name')],
        '#default_value' => $default_values['last_name'],
      ];
    }

    $form['email'] = [
      '#type'          => 'email',
      '#required'      => TRUE,
      '#disabled'      => TRUE,
      '#attributes'    => ['placeholder' => $this->t('Email')],
      '#description'   => $this->t('Please enter email address, read and accept the terms of use of the site.'),
      '#default_value' => $default_values['email'],
    ];

    $form['accept'] = [
      '#type'  => 'checkbox',
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
    if (!$this->newsletter->isContactSubscribed($group)) {
      // Create the subscription for the existing user.
      $this->newsletter->subscribeContact($group);
    }

    $div = '<p>' . $this->t('The subscription has been submitted.') . '</p>';
    $response->addCommand(new ReplaceCommand('#civicrm-newsletter-ajax-form-subscribe-authenticated', $div));

    return $response;
  }

}
