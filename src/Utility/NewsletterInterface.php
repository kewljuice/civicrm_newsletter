<?php

namespace Drupal\civicrm_newsletter\Utility;

/**
 * Central interface for implementing Newsletter.
 */
interface NewsletterInterface {

  /**
   * Gets mailing lists from the CiviCRM api.
   *
   * @return array
   *   An array with result(s).
   */
  public function get();

  /**
   * Gets default mailing lists for current user.
   *
   * @return array
   *   An array with result(s).
   */
  public function getDefault();

  /**
   * Creates mailing subscription(s) via the CiviCRM api.
   *
   * @param array $params
   *   The array with parameters.
   * @param mixed $groups
   *   The newsletter group(s).
   *
   * @return array
   *   An array with result(s).
   */
  public function createSubscription(array $params, $groups);

  /**
   * Updates mailing subscription(s) via the CiviCRM api.
   *
   * @param mixed $groups
   *   The newsletter group(s).
   *
   * @return array
   *   An array with result(s).
   */
  public function updateSubscription($groups);

  /**
   * Checks if the given contact is subscribed to the supplied group(s).
   *
   * @param mixed $groups
   *   The newsletter group(s).
   * @param string $contact_id
   *   The supplied contact_id.
   *   Defaults to 'user_contact_id'.
   *
   * @return bool
   *   Returns if the contact is subscribed to the supplied group(s) or not.
   */
  public function isContactSubscribed($groups, $contact_id = 'user_contact_id');

  /**
   * Subscribes the supplied contact to the supplied group(s).
   *
   * @param mixed $groups
   *   The newsletter group(s).
   * @param string $contact_id
   *   The supplied contact_id.
   *   Defaults to 'user_contact_id'.
   *
   * @return mixed
   *   An array with result(s).
   */
  public function subscribeContact($groups, $contact_id = 'user_contact_id');

  /**
   * Gets the contact information of the supplied contact.
   *
   * @param string $contact_id
   *   The supplied contact_id.
   *   Defaults to 'user_contact_id'.
   *
   * @return mixed
   *   Returns the API result.
   */
  public function getContactDetails($contact_id = 'user_contact_id');

}
