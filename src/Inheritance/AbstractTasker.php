<?php

namespace MagicMonkey\Metasya\Inheritance;

use Exception;

/**
 * Class AbstractTasker
 * @package MagicMonkey\Metasya\Inheritance
 */
abstract class AbstractTasker
{

  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
  /* ### ATTRIBUTES & CONSTRUCTORS ### */
  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

  /**
   * @var string $filePath
   */
  protected $filePath;

  /**
   * @var string $exiftoolPath
   */
  protected $exiftoolPath;


  /**
   * AbstractTasker constructor.
   * @param $filePath
   */
  public function __construct($filePath, $exiftoolPath)
  {
    $this->filePath = $filePath;
    $this->exiftoolPath = $exiftoolPath;
  }

  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
  /* ### FUNCTIONS ### */
  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

  /**
   * Execute a stringified command with exiftool and return its result.
   * @param $stringifiedCmd
   * @param bool $jsonOutput
   * @return array|null|string
   */
  protected function execute($stringifiedCmd, $jsonOutput = false)
  {
    try {
      if (file_exists($this->filePath)) {
        $cmd = $this->trimMultipleWhitespaces($this->exiftoolPath . "exiftool " . (($jsonOutput) ? "-json " : null) . $stringifiedCmd . " " . $this->filePath . " 2>&1");
        $cmdResult = shell_exec($cmd);
        if ($cmdResult == null) {
          if (!$jsonOutput) {
            return ['exiftoolMessage' => trim($cmdResult), 'success' => false];
          } else {
            return null;
          }
        } else {
          if ($this->isJson($cmdResult)) {
            return $this->convertObjectToArray(json_decode($cmdResult)[0]);
          } else {
            return ['exiftoolMessage' => trim($cmdResult), 'success' => true];
          }
        }
      }
      return "Error : file \" " . $this->filePath . " \" not found !";
    } catch (Exception $exception) {
      return $exception->getMessage();
    }
  }

  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
  /* ### TOOLS ### */
  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

  /**
   * Replace the multiple whitespaces by one whitespace.
   * @param $text
   * @return mixed
   */
  protected function trimMultipleWhitespaces($text)
  {
    return trim(preg_replace("/ {2,}/", " ", $text));
  }

  /**
   * Check if a string is json or not (true or false)
   * @param $text
   * @return bool
   */
  protected function isJson($text)
  {
    json_decode($text);
    return (json_last_error() == JSON_ERROR_NONE);
  }

  /**
   * Return json file content as array
   * @param $jsonFilePath
   * @return null|array
   */
  protected function extractJsonFromFile($jsonFilePath)
  {
    if (file_exists($jsonFilePath)) {
      $stringifiedJson = file_get_contents($jsonFilePath);
      return json_decode($stringifiedJson, true)[0];
    }
    return null;
  }

  /**
   * Convert any object to array recursively.
   * @param $obj object
   * @return array
   */
  protected function convertObjectToArray($obj)
  {
    if (is_object($obj)) $obj = (array)$obj;
    if (is_array($obj)) {
      $newArray = [];
      foreach ($obj as $key => $value) {
        $newArray[$key] = $this->convertObjectToArray($value);
      }
    } else $newArray = $obj;
    return $newArray;
  }

  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */
  /* ### GETTERS & SETTERS ### */
  /* :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::: */

  /**
   * @return mixed
   */
  public function getFilePath()
  {
    return $this->filePath;
  }


}
