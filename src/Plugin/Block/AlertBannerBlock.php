<?php

namespace Drupal\localgov_alert_banner\Plugin\Block;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Provides the Alert banner block.
 *
 * @Block(
 *   id = "localgov_alert_banner_block",
 *   admin_label = @Translation("Alert banner"),
 *   category = @Translation("Localgov Alert banner"),
 * )
 */
class AlertBannerBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new AlertBannerBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Create the Alert banner block instance.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Container object.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch the current published banner.
    $published_alert_banners = $this->getCurrentAlertBanners();

    // If no banner found, return NULL so block is not rendered.
    if (empty($published_alert_banners)) {
      return NULL;
    }

    // Render the alert banner.
    $build = [];
    foreach ($published_alert_banners as $published_alert_banner_id) {
      $alert_banner = $this->entityTypeManager->getStorage('localgov_alert_banner')
        ->load($published_alert_banner_id);
      $build[] = $this->entityTypeManager->getViewBuilder('localgov_alert_banner')
        ->view($alert_banner);
    }

    return $build;
  }

  /**
   * Get current alert banner(s).
   *
   * Note: Default order will be by the field type_of_alert
   * (only on the default) and then updated date.
   *
   * @return array
   *   Array with the IDs of any published alert banners.
   */
  protected function getCurrentAlertBanners() {
    $published_alert_banner = $this->entityTypeManager->getStorage('localgov_alert_banner')
      ->getQuery()
      ->condition('status', 1)
      ->sort('type_of_alert', 'DESC')
      ->sort('changed', 'DESC')
      ->execute();
    return $published_alert_banner;
  }

}
