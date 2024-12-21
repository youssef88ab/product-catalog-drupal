<?php

namespace Drupal\Tests\video_filter\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Test basic functionality of Video Filter module.
 *
 * @group video_filter
 */
class Basics extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    // Modules for core functionality.
    'node',
    'filter',

    // This module.
    'video_filter',
  ];

  /**
   * Verify the front page works.
   */
  public function testFrontpage() {
    // Load the front page.
    $this->drupalGet('<front>');
    $this->assertSession()->statusCodeEquals(200);

    // With nothing else configured the front page just has a login form.
    $this->assertSession()->pageTextContains('Enter your Drupal username.');
  }

}
