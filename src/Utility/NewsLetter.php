<?php

namespace Drupal\civicrm_newsletter\Utility;

use Drupal\civicrm\Civicrm;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class NewsLetter.
 */
class NewsLetter implements NewsletterInterface {

  use StringTranslationTrait;

  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Constructs a new NewsLetter object.
   *
   * @param \Drupal\civicrm\Civicrm $civicrm
   *   The CiviCRM service.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The current Drupal user.
   *
   * @throws \Exception
   */
  public function __construct(Civicrm $civicrm, LoggerChannelFactoryInterface $logger) {
    $this->logger = $logger;
    try {
      $this->civicrm = $civicrm;
      $this->civicrm->initialize();
    }
    catch (\Exception $e) {
      $this->logger->get('NewsLetter')
        ->error($e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function api($entity, $action, array $params) {
    $results = [];
    try {
      $results = civicrm_api3($entity, $action, $params);
    }
    catch (\Exception $e) {
      $this->logger->get('NewsLetter')
        ->error($e->getMessage());
    }
    return $results;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefault() {
    // Fetch Mailing groups.
    $list = $this->api('GroupContact', 'get', [
      'sequential' => 1,
      'return' => ['group_id', 'title'],
      'group_type' => 'Mailing List',
      'contact_id' => 'user_contact_id',
      'is_active' => 1,
    ]);
    // Loop API results.
    $group_list = [];
    foreach ($list['values'] as $value) {
      $group_list[$value['group_id']] = $value['title'];
    }
    // Return.
    return $group_list;
  }

  /**
   * {@inheritdoc}
   */
  public function get() {
    // Fetch Mailing groups.
    $list = $this->api('Group', 'get', [
      'sequential' => 1,
      'return' => ['id', 'name', 'title'],
      'group_type' => 'Mailing List',
      'is_active' => 1,
    ]);
    // Loop API results.
    $group_list = [];
    foreach ($list['values'] as $value) {
      $group_list[$value['id']] = $value['title'];
    }
    // Return.
    return $group_list;
  }

  /**
   * {@inheritdoc}
   */
  public function createSubscription($email, $groups) {
    $result = ['email' => $email, 'groups' => $groups];
    // Save Contact.
    $contact = $this->api('Contact', 'Create', [
      'sequential' => 1,
      'contact_type' => 'Individual',
      'source' => $this->t('Newsletter subscription'),
      'email' => $email,
    ]);
    $result['contact_id'] = $contact['id'];
    foreach ($groups as $key => $value) {
      if ($value != 0) {
        // Add contact to group.
        $this->api('GroupContact', 'Create', [
          'group_id' => $key,
          'contact_id' => $contact['id'],
          'status' => 'Added',
        ]);
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function updateSubscription($groups) {
    // Update opt-in(s).
    foreach ($groups as $key => $value) {
      if ($value != 0) {
        $params = [
          'group_id' => $key,
          'contact_id' => 'user_contact_id',
          'status' => 'Added',
        ];
      }
      else {
        $params = [
          'group_id' => $key,
          'contact_id' => 'user_contact_id',
          'status' => 'Removed',
        ];
      }
      // Save params to CiviCRM API.
      $this->api('GroupContact', 'create', $params);
    }
    $result['groups'] = $groups;
    return $result;
  }

}
