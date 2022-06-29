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
  public function createSubscription($params, $groups);

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
