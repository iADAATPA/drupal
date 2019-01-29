<?php

require_once 'XliffDocument.php';

/**
 * Class DrupalMtHubXliffDocument
 *
 * @package Classes
 * @author Colin Harper
 */
class DrupalMtHubXliffDocument extends XliffDocument {

  /**
   * @var MT-Hub Namespace ID
   */
  private $mtHubNamespace = 'drmthub';

  /**
   * @var MT-Hub Namespace Name
   */
  private $mtHubNamespaceName = 'DrupalMtHub';

  public function __construct($data = NULL, $namespaces = NULL) {
    if (empty($namespaces)) {
      $namespaces = [
        $this->getMtHubNamespace() => $this->getMtHubNamespaceName(),
      ];
    }
    parent::__construct($data, $namespaces);
  }

  /**
   * Get the node ID.
   *
   * @return int
   */
  public function getNodeId() {
    $xliff = $this->getXliff();
    $nId = $this->getAttribute($xliff->file->body->{"trans-unit"}, 'node-id', $this->getMtHubNamespace());

    return intval($nId);
  }

  /**
   * Get the Fields in the document as an array of their details.
   *
   * @return array
   */
  public function getFields() {
    $entity_type = 'node';
    // Field details is an array of all fields.
    // Each field has an array of all its details which were in the document.
    $fieldsDetails = [];
    $transUnits = $this->getTransUnits();
    foreach ($transUnits as $transUnit) {
      $fieldsDetails[] = [
        'node_id' => $this->getAttribute($transUnit, 'node-id', $this->getMtHubNamespace()),
        'field_name' => $this->getAttribute($transUnit, 'field-name', $this->getMtHubNamespace()),
        'langcode' => $this->getTargetLanguage(),
        'entity_type' => $entity_type,
        'target' => [$this->getTarget($transUnit)],
        'segment_id' => $this->getAttribute($transUnit, 'segment-id', $this->getMtHubNamespace()),
      ];
    }

    $lastField = [];
    foreach ($fieldsDetails as $fieldDetail) {

      if (empty($lastField)) {
        $lastField = $fieldDetail;
        continue;
      }

      if ($lastField['field_name'] == $fieldDetail['field_name']) {
        $lastField['target'] = array_merge($lastField['target'], $fieldDetail['target']);
      }
      else {
        $fields[] = $lastField;
        $lastField = $fieldDetail;
      }
    }

    $fields[] = $lastField;

    return $fields;
  }

  /**
   * @return string
   */
  public function getMtHubNamespace() {
    return $this->mtHubNamespace;
  }

  /**
   * @param string $mtHubNamespace
   */
  public function setMtHubNamespace($mtHubNamespace) {
    $this->mtHubNamespace = $mtHubNamespace;
  }

  /**
   * @return string
   */
  public function getMtHubNamespaceName() {
    return $this->mtHubNamespaceName;
  }

  /**
   * @param string $mtHubNamespaceName
   */
  public function setMtHubNamespaceName($mtHubNamespaceName) {
    $this->mtHubNamespaceName = $mtHubNamespaceName;
  }
}