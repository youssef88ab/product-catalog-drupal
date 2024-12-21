<?php

namespace Drupal\Tests\footermap\Functional;

use Drupal\Tests\block\Functional\AssertBlockAppearsTrait;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the footermap block as part of a Drupal installation.
 *
 * @group footermap
 */
class FootermapBlockWebTest extends BrowserTestBase {

  use AssertBlockAppearsTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'menu_ui', 'path', 'footermap'];

  /**
   * The block entity.
   *
   * @var \Drupal\block\Entity\Block
   */
  protected $block;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $settings = [
      'label' => 'Footermap',
      'footermap_recurse_limit' => '0',
      'footermap_display_heading' => '1',
      'footermap_avail_menus' => [],
      'footermap_top_menu' => '',
      'region' => 'footer',
    ];

    $this->block = $this->drupalPlaceBlock('footermap_block', $settings);
  }

  /**
   * Assert that the footermap block appears on the front page.
   */
  public function testFrontPage() {
    $this->drupalGet('');
    $this->assertBlockAppears($this->block);
    $this->assertSession()
      ->pageTextContains('Footermap');
    $this->assertSession()
      ->statusCodeEquals(200);
  }

}
