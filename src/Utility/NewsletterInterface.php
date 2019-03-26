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
   * @param string $email
   *   The email address.
   * @param mixed $groups
   *   The newsletter group(s).
   *
   * @return array
   *   An array with result(s).
   */
  public function createSubscription($email, $groups);

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

}
