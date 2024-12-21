<?php

namespace Drupal\better_social_share\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller class for Social Share functionality.
 */
class SocialShareController extends ControllerBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a new SocialShareController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(ModuleHandlerInterface $module_handler, RendererInterface $renderer) {
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
  }

  /**
   * Ajax callback for Social Share functionality.
   */
  public function ajaxCallback() {
    $modulePath = $this->moduleHandler->getModule('better_social_share')->getPath();
    $close_icon = $modulePath . "/images/close.png";
    $platforms = better_social_share_platforms();

    $default_values = [];
    $content = [
      '#theme' => 'social_share_popup',
      '#variables' => [
        'close_icon' => $close_icon,
        'platforms' => $platforms,
      ],
    ];

    // Convert the content to HTML.
    $html = $this->renderer->renderRoot($content);

    // Return the HTML in a JSON response.
    return new JsonResponse(['content' => $html]);
  }

}
