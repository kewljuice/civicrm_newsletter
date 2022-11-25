<?php

namespace Drupal\civicrm_newsletter\Plugin\Block;

use Drupal\civicrm_newsletter\Utility\NewsLetter;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CiviCRM Newsletter' Block for authenticated users.
 *
 * @Block(
 *   id = "block_civicrm_newsletter_authenticated_subscribe",
 *   admin_label = @Translation("CiviCRM Newsletter: Subscribe (for authenticated users)"),
 *   category = @Translation("CiviCRM"),
 * )
 */
class NewsletterAuthenticatedSubscribe extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The Config.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The Newsletter service.
   *
   * @var \Drupal\civicrm_newsletter\Utility\NewsLetterInterface
   */
  protected $newsletter;

  /**
   * The current logged in user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * Constructs a FormBuilder object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The Form Builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory services.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, ConfigFactoryInterface $config_factory, NewsLetter $newsletter, AccountProxyInterface $account) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->config = $config_factory;
    $this->newsletter = $newsletter;
    $this->account = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('config.factory'),
      $container->get('civicrm_newsletter.list'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'manage_subscription_url' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['manage_subscription_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Manage subscription location'),
      '#description' => $this->t('The URL where users can manage their subscriptions (example: /user/profile).
      This is displayed to users when they have already subscribed.
      '),
      '#default_value' => $this->configuration['manage_subscription_url'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['manage_subscription_url'] = $values['manage_subscription_url'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $allowed = $this->config->get('civicrm_newsletter.settings')->get('default');
    // Return form.
    if ($this->newsletter->isContactSubscribed($allowed)) {
      $markup = '<p>' . t('You are already subscribed to our newsletter.') . '</p>';
      if ($this->configuration['manage_subscription_url'] !== '') {
        $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
        $url = '/' . $language . $this->configuration['manage_subscription_url'];
        $markup .= t('<p>You can manage your subscriptions <a href="@link">here</a>.</p>', ['@link' => $url]);
      }

      return [
        '#type' => 'markup',
        '#markup' => $markup,
      ];
    } else {
      return $this->formBuilder->getForm('Drupal\civicrm_newsletter\Form\NewsletterAuthenticatedSubscribe');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Bail if no groups are defined!
    $allowed = $this->config->get('civicrm_newsletter.settings')->get('default');
    if (!isset($allowed) || empty(array_filter($allowed))) {
      return AccessResult::forbidden();
    }
    // Bail if anonymous!
    if ($account->isAnonymous()) {
      // For anonymous, the block is forbidden.
      return AccessResult::forbidden();
    }
    else {
      // For authenticated, the block is allowed.
      return AccessResult::allowed();
    }
  }

}
