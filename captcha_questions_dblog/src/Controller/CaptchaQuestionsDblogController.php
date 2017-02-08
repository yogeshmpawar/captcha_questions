<?php

namespace Drupal\captcha_questions_dblog\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Unicode;

/**
 * Provides route responses for the Example module.
 */
class CaptchaQuestionsDblogController extends ControllerBase {

  /**
   * Fetch and display failed form submissions.
   *
   * @return array
   *   Returns themed table with pager
   */
  public function captchaQuestionsDblogView() {
    $header = [
      array('data' => $this->t('Submission'), 'field' => 'dblogid'),
      array('data' => $this->t('Timestamp'), 'field' => 'timestamp'),
      array('data' => $this->t('IP'), 'field' => 'IP'),
      array('data' => $this->t('Form ID'), 'field' => 'form_id'),
      array('data' => $this->t('Question asked'), 'field' => 'question_asked'),
      array('data' => $this->t('Answer given'), 'field' => 'answer_given'),
      array('data' => $this->t('Correct answer'), 'field' => 'answer_correct'),
    ];
    $rows = [];
    $db = \Drupal::database();
    $query = $db->select('captcha_questions_dblog', 'log');
    $query->fields('log', array(
      'dblogid',
      'timestamp',
      'ip',
      'form_id',
      'question_asked',
      'answer_given',
      'answer_correct',
    ));
    // The actual action of sorting the rows is here.
    $table_sort = $query->extend('Drupal\Core\Database\Query\TableSortExtender')
      ->orderByHeader($header);
    // Limit the rows to 5 for each page.
    $pager = $table_sort->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit(5);
    $result = $pager->execute();

    // Constructing rows from $entries matching $header.
    foreach ($result as $e) {
      $rows[] = array(
        $e->dblogid,
        \Drupal::service('date.formatter')->format($e->timestamp, 'custom', 'Y-m-d H:m:s'),
        $e->ip,
        $e->form_id,
        Unicode::truncate($e->question_asked, '30', TRUE, 20),
        $e->answer_given,
        $e->answer_correct,
      );
    }

    $count = count($rows);
    // The table description.
    $build = array(
      '#markup' => t('Found a total of @count failed submissions', array('@count' => $count)),
    );

    // Generate the table.
    $build['config_table'] = array(
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    );

    // Finally add the pager.
    $build['pager'] = array(
      '#type' => 'pager',
    );

    return $build;
  }

}
