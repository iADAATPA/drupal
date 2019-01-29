<?php

/**
 * @file
 * Administration functions for mt_hub module.
 *
 * @author Colin Harper
 */

/**
 * Page callback: MT-Hub settings
 *
 * @see mt_hub_menu()
 */
function mt_hub_admin_form($form, &$form_state) {
  $form['mt_hub_token'] = [
    '#type' => 'textfield',
    '#title' => t('API Token'),
    '#default_value' => variable_get('mt_hub_token', 'xxxxxxxxxx'),
    '#size' => 20,
    '#maxlength' => 20,
    '#description' => t('Your MT-Hub Consumer API token.'),
    '#required' => TRUE,
  ];

  return system_settings_form($form);
}

/**
 * Content Translate table
 *
 */
function mt_hub_content_translate_screen() {
  $siteName = variable_get('site_name');
  $siteName = strtolower($siteName);
  $siteName = preg_replace('/[ ]/', '_', $siteName);

  variable_set('site_name_programmatic', $siteName);

  drupal_static_reset('language_list');
  $languages = language_list('language');
  $noLanguages = count($languages);

  // The minimum number of rows including their translations.
  $minimum = 20;

  // configure the table header columns
  $header = [
    [
      'data' => 'Title',
      'field' => 'title',
      'sort' => 'ASC',
    ],
    [
      'data' => 'Type',
      'field' => 'type',
      'sort' => 'ASC',
    ],
    [
      'data' => 'Source Language',
      'sort' => 'ASC',
    ],
    [
      'data' => 'Language',
      'colspan' => '2',
    ],
    [
      'data' => 'Action',
    ],
    [
      'data' => 'Post-Edit',
    ],
  ];

  $select = db_select('node', 'n')
    ->extend('PagerDefault')
    ->extend('TableSort');

  $select->fields('n')
    ->limit(ceil($minimum / $noLanguages))
    ->orderByHeader($header);

  $results = $select->execute();

  $rows = [];

  foreach ($results as $row) {
    foreach ($languages as $langcode => $language) {
      if ($row->language == $langcode) {
        continue;
      }
      $uniqueId = REQUEST_TIME . drupal_random_key(5);
      $rows[] = [
        $row->title,
        $row->type,
        $row->language,
        $language->name,
        $langcode,
        ['data' => drupal_get_form('mt_hub_translate_content_form', $row->nid, $langcode, $uniqueId)],
        [
          'data' => [
            '#type' => 'link',
            '#title' => t('export'),
            '#href' => 'export/' . $row->nid . '/' . $langcode,
            '#options' => ['attributes' => ['title' => t('Export the translated content for this node in an XLIFF document')]],
          ],
        ],
      ];
    }
  }

  $output = theme('table', [
    'header' => $header,
    'rows' => $rows,
  ]);

  $output .= theme('pager');

  return $output;
}

/**
 * MT-Hub Translate Content Form
 *
 * @param $form
 * @param $form_state
 * @param $nId
 * @param $langcode
 * @param $uniqueId
 *
 * @return array
 */
function mt_hub_translate_content_form(
  $form,
  &$form_state,
  $nId,
  $langcode,
  $uniqueId
) {
  $form = [];

  // Defines the ajaxified link
  $form['translate_link'] = [
    '#type' => 'link',
    '#title' => 'translate',
    // set the ajax properties
    '#ajax' => [
      'wrapper' => 'progress-status-' . $uniqueId,
      'method' => 'html',
      // Tell FormAPI about our progress bar.
      'progress' => [
        'type' => 'bar',
        'message' => t('Translate...'),
        // Have the progress bar access this URL path.
        'url' => url('mt_hub/translate/progressbar/progress/' . $uniqueId),
        // The time interval for the progress bar to check for updates.
        'interval' => 1000,
      ],
    ],
    // the url of the ajax callback as defined in the hook_menu
    '#href' => 'mt_hub/translate/content/' . $nId . '/' . $langcode . '/' . $uniqueId,
  ];

  return $form;
}
