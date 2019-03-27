<?php

namespace Drupal\civicrm_newsletter\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'CiviCRM Newsletter' Block.
 *
 * @Block(
 *   id = "block_civicrm_newsletter_manage",
 *   admin_label = @Translation("CiviCRM Newsletter: Manage"),
 *   category = @Translation("CiviCRM"),
 * )
 */
class NewsletterManage extends BlockBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilderInterface $form_builder, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->config = $config_factory;
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
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Return form.
    return $this->formBuilder->getForm('Drupal\civicrm_newsletter\Form\NewsletterManage');
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    // Bail if no groups are defined!
    $allowed = $this->config->get('civicrm_newsletter.settings')->get('manage');
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
