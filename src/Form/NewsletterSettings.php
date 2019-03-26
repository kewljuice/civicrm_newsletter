<?php

namespace Drupal\civicrm_newsletter\Form;

use Drupal\civicrm_newsletter\Utility\NewsletterInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NewsletterSettings.
 */
class NewsletterSettings extends ConfigFormBase {

  /**
   * The Newsletter service.
   *
   * @var \Drupal\civicrm_newsletter\Utility\NewsLetterInterface
   */
  protected $newsletter;

  /**
   * Constructor.
   *
   * @param \Drupal\civicrm_newsletter\Utility\NewsLetterInterface $newsletter
   *   The Newsletter service.
   */
  public function __construct(NewsLetterInterface $newsletter) {
    $this->newsletter = $newsletter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('civicrm_newsletter.list')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'civicrm_newsletter.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'civicrm_newsletter_form_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Fetch default parameters.
    $config = $this->config('civicrm_newsletter.settings');
    $list = $this->newsletter->get();
    $form['default'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default opt-in group (Subscribe)'),
      '#options' => $list,
      '#default_value' => (NULL !== $config->get('default') ? $config->get('default') : []),
    ];
    $form['manage'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Default opt-in group (Manage)'),
      '#options' => $list,
      '#default_value' => (NULL !== $config->get('manage') ? $config->get('manage') : []),
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
    // Save parameters.
    $this->config('civicrm_newsletter.settings')
      ->set('default', $form_state->getValue('default'))
      ->set('manage', $form_state->getValue('manage'))
      ->save();
  }

}
