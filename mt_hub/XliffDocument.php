<?php

class XliffDocument {

  // Set this to true to generate a log
  const DEBUG = FALSE;

  /**
   * @var string A well-formed XML string
   */
  private $data;

  /**
   * @var SimpleXMLElement
   */
  private $xliff;

  /**
   * @var string
   */
  private $xmlVersion = '1.0';

  /**
   * @var string
   */
  private $xliffVersion = '1.2';

  /**
   * @var string
   */
  private $xliffXmlns = 'urn:oasis:names:tc:xliff:document:1.2';

  /**
   * @var string
   */
  private $fileDatatype = 'plaintext';

  /**
   * @var string
   */
  private $filename = 'mt_hub_export';

  public function __construct($data = NULL, $namespaces = NULL) {
    if (isset($data)) {
      $this->data = $data;
      $this->xliff = new SimpleXMLElement($data);
    }
    else {
      $this->xliff = $this->generateBasic($namespaces);
    }
  }

  private static function debug($msg) {
    if (self::DEBUG) {
      error_log($msg);
    }
  }

  /**
   * Generate the basic XLIFF format file.
   *
   * @param array $namespaces
   *
   * @return \SimpleXMLElement
   */
  private function generateBasic($namespaces) {
    $data = "<?xml version=\"{$this->getXmlVersion()}\"?>";
    $data .= "<xliff version=\"{$this->getXliffVersion()}\" xmlns=\"{$this->getXliffXmlns()}\"";
    if (!empty($namespaces) && is_array($namespaces)) {
      foreach ($namespaces as $key => $value) {
        $data .= " xmlns:$key=\"$value\"";
      }
    }
    $data .= ">";
    $data .= "<file original=\"\" datatype=\"{$this->getFileDatatype()}\">";
    $data .= "<body>";
    $data .= "</body>";
    $data .= "</file>";
    $data .= "</xliff>";

    $xliff = new SimpleXMLElement($data);

    return $xliff;
  }

  /**
   * Add a trans-unit to the document.
   *
   * @param array $attributes
   * @param string|null $source
   * @param string|null $target
   *
   * @return $this
   */
  public function addTransUnit($attributes, $source = NULL, $target = NULL) {
    $transUnit = $this->xliff->file->body->addChild('trans-unit');

    if (is_array($attributes)) {
      foreach ($attributes as $attribute) {
        $transUnit->addAttribute($attribute[0], $attribute[1], $attribute[2]);
      }
    }

    if (isset($target)) {
      $transUnit->addChild('source', $source);
    }

    if (isset($target)) {
      $transUnit->addChild('target', $target);
    }

    return $this;
  }

  /**
   * Get the source language of the document.
   *
   * @return null|string
   */
  public function getSourceLanguage() {
    $sourceLanguage = $this->getAttribute($this->xliff->file, 'source-language');

    return $sourceLanguage;
  }

  /**
   * Set the source language of the document.
   *
   * @param $sourceLanguage
   *
   * @return $this
   */
  public function setSourceLanguage($sourceLanguage) {
    $this->xliff->file->addAttribute('source-language', $sourceLanguage);

    return $this;
  }

  /**
   * Get the target language of the document.
   *
   * @return null|string
   */
  public function getTargetLanguage() {
    $targetLanguage = $this->getAttribute($this->xliff->file, 'target-language');

    return $targetLanguage;
  }

  /**
   * Set the target language of the document.
   *
   * @param $targetLanguage
   *
   * @return $this
   */
  public function setTargetLanguage($targetLanguage) {
    $this->xliff->file->addAttribute('target-language', $targetLanguage);

    return $this;
  }

  /**
   * Download the document.
   */
  public function download() {
    $xml = html_entity_decode($this->xliff->asXML());

    header('Content-Disposition: attachment; filename="' . $this->getFilename() . '.xliff"');
    header('Content-Type: text/xliff');
    header('Content-Length: ' . strlen($xml));
    header('Connection: close');

    echo $xml;
  }

  /**
   * Get the the trans-units.
   *
   * @return mixed
   */
  public function getTransUnits() {
    return $this->getXliff()->file->body->children();
  }

  /**
   * Get the attribute from the element.
   *
   * Optional namespace selection.
   *
   * @param \SimpleXMLElement $element
   * @param string $attribute
   * @param string|null $namespace
   *
   * @return null|string
   */
  public function getAttribute($element, $attribute, $namespace = NULL) {
    // var_dump($element);
    if (isset($namespace)) {
      $attributes = $element->attributes($namespace, TRUE);
    }
    else {
      $attributes = $element->attributes();
    }
    // var_dump($attributes);

    if (isset($attributes[$attribute])) {
      return (string) $attributes[$attribute];
    }

    return NULL;
  }

  /**
   * Get the source string from the element.
   *
   * @param \SimpleXMLElement $transUnit
   *
   * @return string
   */
  public function getSource($transUnit) {
    $source = (string) $transUnit->source;

    return $source;
  }

  /**
   * Get the target from the element.
   *
   * @param \SimpleXMLElement $transUnit
   *
   * @return string
   */
  public function getTarget($transUnit) {
    $target = (string) $transUnit->target;

    return $target;
  }

  /**
   * @return SimpleXMLElement
   */
  public function getXliff() {
    return $this->xliff;
  }

  /**
   * @param SimpleXMLElement $xliff
   */
  public function setXliff($xliff) {
    $this->xliff = $xliff;
  }

  /**
   * @return string
   */
  public function getFilename() {
    return $this->filename;
  }

  /**
   * @param string $filename
   */
  public function setFilename($filename) {
    $this->filename = $filename;
  }

  /**
   * @return string
   */
  public function getData() {
    return $this->data;
  }

  /**
   * @param string $data
   */
  public function setData($data) {
    $this->data = $data;
  }

  /**
   * @return string
   */
  public function getXmlVersion() {
    return $this->xmlVersion;
  }

  /**
   * @param string $xmlVersion
   */
  public function setXmlVersion($xmlVersion) {
    $this->xmlVersion = $xmlVersion;
  }

  /**
   * @return string
   */
  public function getXliffVersion() {
    return $this->xliffVersion;
  }

  /**
   * @param string $xliffVersion
   */
  public function setXliffVersion($xliffVersion) {
    $this->xliffVersion = $xliffVersion;
  }

  /**
   * @return string
   */
  public function getXliffXmlns() {
    return $this->xliffXmlns;
  }

  /**
   * @param string $xliffXmlns
   */
  public function setXliffXmlns($xliffXmlns) {
    $this->xliffXmlns = $xliffXmlns;
  }

  /**
   * @return string
   */
  public function getFileDatatype() {
    return $this->fileDatatype;
  }

  /**
   * @param string $fileDatatype
   */
  public function setFileDatatype($fileDatatype) {
    $this->fileDatatype = $fileDatatype;
  }
}