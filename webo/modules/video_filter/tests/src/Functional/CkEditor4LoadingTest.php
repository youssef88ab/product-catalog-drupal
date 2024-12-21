<?php

namespace Drupal\Tests\video_filter\Functional;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests loading of CKEditor 4 with the video filter button.
 *
 * @group video_filter
 */
class CkEditor4LoadingTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'filter',
    'editor',
    'ckeditor',
    'node',
    'video_filter',
  ];

  /**
   * A normal user with access to the format with ckeditor.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create text format, associate CKEditor.
    $filtered_html_format = FilterFormat::create([
      'format' => 'filtered_html',
      'name' => 'Filtered HTML',
      'weight' => 0,
      'filters' => [],
    ]);
    $filtered_html_format->save();
    $editor = Editor::create([
      'format' => 'filtered_html',
      'editor' => 'ckeditor',
    ]);
    $editor->save();

    // Create node type.
    $this->drupalCreateContentType([
      'type' => 'article',
      'name' => 'Article',
    ]);

    // Create a user.
    $this->user = $this->drupalCreateUser([
      'create article content',
      'edit any article content',
      'use text format filtered_html',
    ]);
  }

  /**
   * Tests loading of CKEditor CSS, JS and JS settings.
   */
  public function testLoading() {
    // Add the video button to the CKEditor.
    $filtered_html_editor = Editor::load('filtered_html');
    $settings = $filtered_html_editor->getSettings();
    $settings['toolbar']['rows'] = [
      0 => [
        0 => [
          'name' => 'Tools',
          'items' => [
            'video_filter',
          ],
        ],
      ],
    ];
    $filtered_html_editor->setSettings($settings)->save();

    $this->drupalLogin($this->user);
    $this->drupalGet('node/add/article');

    // Check if the CKEditor is displayed on the page.
    [$settings, $editor_settings_present, $editor_js_present] = $this->getThingsToCheck();
    $ckeditor_plugin = $this->container->get('plugin.manager.editor')->createInstance('ckeditor');
    $editor = Editor::load('filtered_html');
    $expected = [
      'formats' => [
        'filtered_html' => [
          'format' => 'filtered_html',
          'editor' => 'ckeditor',
          'editorSettings' => $ckeditor_plugin->getJSSettings($editor),
          'editorSupportsContentFiltering' => TRUE,
          'isXssSafe' => FALSE,
        ],
      ],
    ];
    $this->assertTrue($editor_settings_present, "Text Editor module's JavaScript settings are on the page.");
    $this->assertEquals($expected, $settings['editor'], "Text Editor module's JavaScript settings on the page are correct.");
    $this->assertTrue($editor_js_present, 'Text Editor JavaScript is present.');
    $this->assertSession()->fieldExists('edit-body-0-value');
    $this->assertContains('ckeditor/drupal.ckeditor', explode(',', $settings['ajaxPageState']['libraries']), 'CKEditor glue library is present.');

    $editor_settings = $this->getDrupalSettings()['editor']['formats']['filtered_html']['editorSettings'];
    $this->assertEquals('video_filter', $editor_settings['toolbar'][0]['items'][0]);
  }

  /**
   * Returns the things to check to be present on the page.
   *
   * @return array
   *   A list of things to check to be available on the page.
   */
  protected function getThingsToCheck() {
    $settings = $this->getDrupalSettings();
    return [
      // JavaScript settings.
      $settings,
      // Editor.module's JS settings present.
      isset($settings['editor']),
      // Editor.module's JS present. Note: ckeditor/drupal.ckeditor depends on
      // editor/drupal.editor, hence presence of the former implies presence of
      // the latter.
      isset($settings['ajaxPageState']['libraries']) && in_array('ckeditor/drupal.ckeditor', explode(',', $settings['ajaxPageState']['libraries'])),
    ];
  }

}
