<?php

namespace Drupal\civicrm_newsletter\Form;

use Drupal\civicrm_newsletter\Utility\NewsletterInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterManage.
 */
class NewsletterManage extends FormBase {

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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('civicrm_newsletter.list')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civicrm_newsletter_form_manage';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Parameters.
    $list = $this->newsletter->get();
    $allowed = $this->config('civicrm_newsletter.settings')->get('manage');
    // Limit list to selection.
    foreach ($list as $key => $value) {
      if ($allowed[$key] == 0) {
        unset($list[$key]);
      }
    }
    $default = $this->newsletter->getDefault();
    // Form components.
    $form['list'] = [
      '#type' => 'checkboxes',
      '#options' => $list,
      '#description' => $this->t('Manage your newsletter subscriptions.'),
      '#default_value' => array_keys($default),
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Parameters.
    $groups = $form_state->getValue('list');
    // Update.
    $this->newsletter->updateSubscription($groups);
    // Display the results.
    $this->messenger->addMessage($this->t('The subscription has been submitted.'));
  }

}
