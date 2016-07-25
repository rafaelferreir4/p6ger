<?php

/**
 * @file
 * Contains \Drupal\p6\Plugin\Block\NextPreviousBlock.
 */

namespace Drupal\p6\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;


/**
 * Provides a 'Next Previous' block.
 *
 * @Block(
 *   id = "next_previous_block",
 *   admin_label = @Translation("Next Previous Block"),
 *   category = @Translation("Blocks")
 * )
 */
class NextPreviousBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    //Get the created time of the current node
    $node = \Drupal::request()->attributes->get('node');
    $created_time = $node->getCreatedTime();
    $link = "";

    $link .= $this->generatePrevious($created_time);
    $link .= $this->generateNext($created_time);

    return array('#markup' => $link);
  }

  /**
   * Lookup the previous node, i.e. youngest node which is still older than the node
   * currently being viewed.
   *
   * @param  string $created_time A unix time stamp
   * @return string               an html link to the previous node
   */
  private function generatePrevious($created_time) {
    return $this->generateNextPrevious('prev', $created_time);
  }

  /**
   * Lookup the next node, i.e. oldest node which is still younger than the node
   * currently being viewed.
   *
   * @param  string $created_time A unix time stamp
   * @return string               an html link to the next node
   */
  private function generateNext($created_time) {
    return $this->generateNextPrevious('next', $created_time);
  }

  /**
   * Lookup the next or previous node
   *
   * @param  string $direction    either 'next' or 'previous'
   * @param  string $created_time a Unix time stamp
   * @return string               an html link to the next or previous node
   */
  private function generateNextPrevious($direction = 'next', $created_time) {
    if ($direction === 'next') {
      $comparison_opperator = '<';
      $sort = 'DESC';
    }
    elseif ($direction === 'prev') {
      $comparison_opperator = '>';
      $sort = 'ASC';
    }

    //Lookup 1 node younger (or older) than the current node
    $query = \Drupal::entityQuery('node');
    $next = $query->condition('created', $created_time, $comparison_opperator)
      ->condition('type', 'work')
      ->sort('created', $sort)
      ->range(0, 1)
      ->execute();

    //If this is not the youngest (or oldest) node
    if (!empty($next) && is_array($next)) {
      $next = array_values($next);
      $next = $next[0];

      // Get the TID from the client name.
      $query = \Drupal::database()->select('node__field_client_name', 'cn');
      $query->addField('cn', 'field_client_name_target_id');
      $query->condition('cn.entity_id', $next);
      $query->range(0, 1);
      $tid = $query->execute()->fetchField();

      // Load the taxonomy.
      $query = \Drupal::database()->select('taxonomy_term_field_data', 'tfd');
      $query->addField('tfd', 'name');
      $query->condition('tfd.tid', $tid);
      $query->range(0, 1);
      $client = $query->execute()->fetchField();

      //Find the alias of the next node
      $next_url = \Drupal::service('path.alias_manager')->getAliasByPath("/node/" . $next);

      //Build the URL of the next node
      $next_url = Url::fromUri('internal:' . $next_url, ['attributes' => ['class' => [$direction]]]);

      //Build the HTML for the next node
      return \Drupal::l($client, $next_url);
    }
  }
}
