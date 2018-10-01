<?php

require_once 'XliffDocument.php';

class DrupalIadaatpaXliffDocument extends XliffDocument {
  private $iadaatpaNamespace = 'dria';
  private $iadaatpaNamespaceName = 'DrupalIadaatpa';

  public function __construct($data = null, $namespaces = null) {
    if (empty($namespaces)) {
      $namespaces = [
        $this->getIadaatpaNamespace() => $this->getIadaatpaNamespaceName()
      ];
    }
    parent::__construct($data, $namespaces);
  }

  /**
   * Get the node ID.
   *
   * @return int
   */
  public function getNodeId()
  {
    $xliff = $this->getXliff();
    $nId = $this->getAttribute($xliff->file->body->{"trans-unit"}, 'node-id', $this->getIadaatpaNamespace());

    return intval($nId);
  }

  /**
   * Get the Fields in the document as an array of their details.
   *
   * @return array
   */
  public function getFields()
  {
    $entity_type = 'node';
    // Filed details is an array of all fields.
    // Each field has an array of all its details which were in the document.
    $fieldsDetails = [];
    $transUnits = $this->getTransUnits();
    foreach ($transUnits as $transUnit) {
      $fieldsDetails[] = [
        'node_id' => $this->getAttribute($transUnit, 'node-id', $this->getIadaatpaNamespace()),
        'field_name' => $this->getAttribute($transUnit, 'field-name', $this->getIadaatpaNamespace()),
        'langcode' => $this->getTargetLanguage(),
        'entity_type' => $entity_type,
        'target' => [$this->getTarget($transUnit)],
        'segment_id' => $this->getAttribute($transUnit, 'segment-id', $this->getIadaatpaNamespace())
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
      } else {
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
  public function getIadaatpaNamespace()
  {
    return $this->iadaatpaNamespace;
  }

  /**
   * @param string $iadaatpaNamespace
   */
  public function setIadaatpaNamespace($iadaatpaNamespace)
  {
    $this->iadaatpaNamespace = $iadaatpaNamespace;
  }

  /**
   * @return string
   */
  public function getIadaatpaNamespaceName() {
    return $this->iadaatpaNamespaceName;
  }

  /**
   * @param string $iadaatpaNamespaceName
   */
  public function setIadaatpaNamespaceName($iadaatpaNamespaceName)
  {
    $this->iadaatpaNamespaceName = $iadaatpaNamespaceName;
  }
}