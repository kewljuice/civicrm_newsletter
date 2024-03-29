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
      'options' => [
        'limit' => 1000,
      ],
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
      'options' => [
        'limit' => 1000,
      ],
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
  public function createSubscription($params, $groups) {
    $result = [
      'params' => $params,
      'groups' => $groups,
    ];

    $default = [
      'sequential' => 1,
      'contact_type' => 'Individual',
      'source' => $this->t('Newsletter subscription'),
    ];

    // Save Contact.
    $contact = $this->api('Contact', 'Create', array_merge($default, $params));

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

  /**
   * {@inheritdoc}
   */
  public function isContactSubscribed($groups, $contact_id = 'user_contact_id') {
    $subscribed = TRUE;

    foreach ($groups as $key => $value) {
      if ($value != 0) {
        $result = $this->api('Contact', 'get', [
          'sequential' => 1,
          'group' => $key,
          'id' => $contact_id,
        ]);

        // If we found a group and the contact was not in it,
        // he is not subscribed to all available groups.
        if (empty($result['values'])) {
          $subscribed = FALSE;
          break;
        }
      }
    }
    return $subscribed;
  }

  /**
   * {@inheritdoc}
   */
  public function subscribeContact($groups, $contact_id = 'user_contact_id') {
    $result = [];

    foreach ($groups as $key => $value) {
      if ($value != 0) {
        // Add contact to group.
        $result[] = $this->api('GroupContact', 'Create', [
          'group_id' => $key,
          'contact_id' => $contact_id,
          'status' => 'Added',
        ]);
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getContactDetails($contact_id = 'user_contact_id') {
    return $this->api('Contact', 'get', [
      'sequential' => 1,
      'id' => $contact_id,
    ]);
  }

}
