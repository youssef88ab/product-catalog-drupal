<?php
/**
 * @author: Rene Bakx (rene@renebakx.nl)
 * @date: 04-11-2016
 * @description A views output filter that allows the first xx result in different viewmode then the rest.
 */

namespace Drupal\views_split\Plugin\views\style;

use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\style\StylePluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @ingroup views_style_plugins
 *
 * @ViewsStyle(
 *   id = "splitview",
 *   title = @Translation("Split view"),
 *   help = @Translation("Renders the first xx rows in different viewmode"),
 *   theme = "views_view_splitview",
 *   display_types = {"normal"}
 * )
 */
class Splitview extends StylePluginBase {

  /**
   * Does the style plugin allows to use style plugins.
   *
   * @var bool
   */
  protected $usesRowPlugin = TRUE;

  /**
   * Does the style plugin support custom css class for the rows.
   *
   * @var bool
   */
  protected $usesRowClass = TRUE;


  protected $usesFields = FALSE;

  /**
   * @var EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * The roles page setting form.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param EntityDisplayRepository $entityDisplayRepository
   *   The entity display repository.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityDisplayRepository $entityDisplayRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityDisplayRepository = $entityDisplayRepository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options['view_mode'] = ['default' => 'teaser'];
    $options['first_x_value'] = ['default' => 0];
    $options['first_page_only'] = ['default' => 1];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['#pre_render'][] = array(
      get_class($this),
      'preRenderAddFieldsetMarkup'
    );
    // Not sure if this sticks..
    if (FALSE == $entity_type = $this->view->storage->getExecutable()->rowPlugin->getEntityTypeId()) {
      $entity_type = 'node';
    }

    $form['uses_fields']['#type'] = 'hidden';

    $form['view_mode'] = [
      '#type' => 'select',
      '#options' => $this->entityDisplayRepository->getViewModeOptions($entity_type),
      '#description' => $this->t('The viewmode to use for the first entries'),
      '#title' => $this->t('View mode'),
      '#default_value' => $this->options['view_mode'],
    ];

    $form['first_x_value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Split limit'),
      '#description' => $this->t('The amount of results to render in the given view mode before falling back to the original view mode'),
      '#default_value' => $this->options['first_x_value'],
    ];

    $form['first_page_only'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('First page only'),
      '#description' => $this->t('Only split the first items on page 1.'),
      '#default_value' => $this->options['first_page_only']
    ];

  }

  public function renderGroupingSets($sets, $level = 0) {
    $output = array();
    $viewMode = $this->options['view_mode'];
    $splitAt = $this->options['first_x_value'];
    $firstPageOnly = boolval($this->options['first_page_only']);
    $isFirstPage = ($this->view->pager && $this->view->pager->getCurrentPage() == 0);

    foreach ($sets as $set) {
      $level = isset($set['level']) ? $set['level'] : 0;

      if ($this->usesRowPlugin()) {
        foreach ($set['rows'] as $index => $row) {
          $this->view->row_index = $index;
          $render = $this->view->rowPlugin->render($row);
          if ($firstPageOnly && $isFirstPage) {
            if ($index < $splitAt) {
              $render = $this->swapViewMode($render, $viewMode);
            }
          }
          $set['rows'][$index] = $render;
        }
      }
      $single_output = $this->renderRowGroup($set['rows']);
      $single_output['#grouping_level'] = $level;
      $single_output['#title'] = $set['group'];
      $output[] = $single_output;
    }

    unset($this->view->row_index);
    return $output;
  }

  /**
   * Swaps the viewmode on a render array.
   * @param $render
   * @param $newViewMode
   */
  private function swapViewMode($render, $newViewMode) {
    $original_viewmode = $render['#view_mode'];
    $render['#view_mode'] = $newViewMode;
    array_walk($render['#cache']['keys'], function (&$value) use ($original_viewmode, $newViewMode) {
      if ($value == $original_viewmode) {
        $value = $newViewMode;
      }
    });
    return $render;
  }
}
