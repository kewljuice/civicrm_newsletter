<?php

namespace Drupal\civicrm_newsletter\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CiviCRM Newsletter' Block.
 *
 * @Block(
 *   id = "block_civicrm_newsletter_subscribe_name",
 *   admin_label = @Translation("CiviCRM Newsletter: Subscribe (Name and Email)"),
 *   category = @Translation("CiviCRM"),
 * )
 */
class NewsletterSubscribeName extends BlockBase implements ContainerFactoryPluginInterface {

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
   * The current logged in user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $account;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

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
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   *   The account interface.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, ConfigFactoryInterface $config_factory, AccountProxyInterface $account, MessengerInterface $messenger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->config = $config_factory;
    $this->account = $account;
    $this->messenger = $messenger;
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
      $container->get('current_user'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'ajax_form' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['ajax_form'] = [
      '#type'          => 'checkbox',
      '#title'         => $this->t('Ajax Form'),
      '#description'   => $this->t('Use AJAX to prevent a page reloads upon submission.'),
      '#default_value' => $this->configuration['ajax_form'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['ajax_form'] = $values['ajax_form'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($this->account->id() == 1) {
      $this->messenger->addMessage(
        $this->t(
          "Displaying newsletter subscribe block on this page as a result of the current user's unique permissions."
        ),
        $this->messenger::TYPE_STATUS
      );
    }

    // Return form.
    if ($this->configuration['ajax_form']) {
      return $this->formBuilder->getForm('Drupal\civicrm_newsletter\Form\NewsletterAjaxSubscribeName');
    }
    else {
      return $this->formBuilder->getForm('Drupal\civicrm_newsletter\Form\NewsletterSubscribeName');
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
    // Bail if authenticated!
    if ($account->isAuthenticated() && $account->id() != '1') {
      // For anonymous, the block is forbidden.
      return AccessResult::forbidden();
    }
    else {
      // For authenticated, the block is allowed.
      return AccessResult::allowed();
    }
  }

}
