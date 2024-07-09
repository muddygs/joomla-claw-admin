<?php

namespace ClawCorpLib\Helpers;

use Exception;
use Joomla\Database\DatabaseInterface;

/**
 * Writes binary blob data from db column to file to allow for linking in HTML.
 * @package ClawCorpLib\Helpers
 */
class DbBlob
{
  public function __construct(
    private DatabaseInterface $db,
    private string $cacheDir,
    private string $prefix = '',
    private string $extension = '',
    private string $key = 'id'
  ) {}

  /**
   * Save binary blob data from db column to file.
   * @param string $tableName DB Table containing the blob
   * @param array $rowIds Row "id" values to process
   * @param string $key Column name containing the blobs
   * @param array $minAges (optional) Parallel array of DateTime objects to compare against cache file modification times;
   * if the cache file is newer than the DateTime object, the cache file will not be updated. Default "now".
   
   * @return array Filenames indexed by rowId
   * @throws Exception 
   */
  public function toFile(string $tableName, array $rowIds, string $key, array $minAges = []): array
  {
    $this->createCacheDir();
    $filenames = [];

    if (!empty($minAges) && count($minAges) != count($rowIds)) {
      throw new Exception('minAges array must be empty or have the same number of elements as rowIds');
    }

    if (empty($minAges)) {
      $minAge = new \DateTime('now', new \DateTimeZone('UTC'));

      // create minAges array with $rowIds as keys
      $minAges = array_fill_keys($rowIds, $minAge);
    } else {
      // map keys of rowIds to minAges
      $minAges = array_combine($rowIds, $minAges);
    }

    // If the extension is not blank, we assume the caller has prior knowledge of the blob data
    // and we will not assess the mime type and delay loading the blob data until the cache file
    // is assessed for existence and age.

    foreach ($rowIds as $rowId) {
      if ($this->extension != '') {
        $blob = '';
        $cacheFilename = $this->getFilename($rowId, $blob);
      } else {
        $blobData = $this->getBlobData($tableName, $rowId, $key);

        if ($blobData === false) {
          continue;
        }

        $cacheFilename = $this->getFilename($rowId, $blobData);
      }

      if (is_null($cacheFilename)) {
        continue;
      }

      if (
        file_exists($cacheFilename) &&
        filemtime($cacheFilename) > $minAges[$rowId]->getTimestamp()
      ) {
        $filenames[$rowId] = $this->toRelativePath($cacheFilename);
        continue;
      }

      if ($this->extension != '') {
        $blobData = $this->getBlobData($tableName, $rowId, $key);
        if (is_null($blobData)) {
          continue;
        }
      }

      if (file_put_contents($cacheFilename, $blobData) !== false) {
        $filenames[$rowId] = $this->toRelativePath($cacheFilename);
      }
    }

    return $filenames;
  }

  private function toRelativePath(string $path): string
  {
    $relativePath = str_replace(JPATH_ROOT, '', $path);
    if (substr($relativePath, 0, 1) == '/') {
      $relativePath = substr($relativePath, 1);
    }

    return $relativePath;
  }

  private function createCacheDir()
  {
    if (!is_dir($this->cacheDir)) {
      if (!mkdir($this->cacheDir, 0775, true)) {
        throw new Exception('Failed to create cache directory');
      }
    }
  }

  private function getBlobData($tableName, $rowId, $key): ?string
  {
    $query = $this->db->getQuery(true)
      ->select($key)
      ->from($this->db->quoteName($tableName))
      ->where($this->db->quoteName($this->key) . ' = ' . $this->db->quote($rowId));
    $result = $this->db->setQuery($query)->loadResult();
    return $result;
  }

  private function getFilename($rowId, &$blobData): ?string
  {
    if ($this->extension != '') {
      return $this->cacheDir . '/' . ($this->prefix ? $this->prefix : '') . $rowId . '.' . $this->extension;
    }

    // Create a fileinfo resource
    $finfo = finfo_open(FILEINFO_MIME_TYPE);

    if ($finfo) {
      // Detect the MIME type of the binary blob
      $mimeType = strtolower(finfo_buffer($finfo, $blobData));

      // Close the fileinfo resource
      finfo_close($finfo);
    } else {
      return null;
    }

    // TODO: Linux/LSB only? Good enough for this for now.
    $extension = $this->getMimeExtension($mimeType);

    return is_null($extension) ? null : $this->cacheDir . '/' . ($this->prefix ? $this->prefix : '') . $rowId . '.' . $extension;
  }

  private function getMimeExtension(string $mimeType): ?string
  {
    $extension = '';

    $file = '/etc/mime.types';

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if ($lines === false) {
      throw new Exception('Failed to read mime.types file');
    }

    foreach ($lines as $line) {
      // Ignore comments and lines starting with a space or tab
      if (strpos($line, '#') === 0 || preg_match('/^\s/', $line)) {
        continue;
      }

      $parts = preg_split('/\s+/', $line);

      // Ignore entries without explicitly defined extensions
      if (count($parts) < 2) {
        continue;
      }

      $mime = strtolower(array_shift($parts));

      if ($mime !== $mimeType) {
        continue;
      }

      $extension = $parts[0];

      if (count($parts) > 1) {
        // Some entries have multiple extensions (e.g. 'text/plain txt asc')
        foreach ($parts as $ext) {
          if (strlen($ext) == 3) {
            $extension = $ext;
            break 2;
          }
        }
      }
    }

    return $extension == '' ? null : $extension;
  }
}
