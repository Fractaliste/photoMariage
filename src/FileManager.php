<?php

namespace Raphdine;

/**
 * Description of FileManager
 *
 * @author Raphiki
 */
class FileManager {

    private $realDir;
    private $relativeDir;
    private $recursive;
    private $folders = array();
    private $files = array();

    function __construct($relativeDir = '', $recursive = false) {
        $this->recursive = $recursive;
        $this->realDir = __DIR__ . '/../web/img/mariage/';
        if ($relativeDir === '' || substr($relativeDir, - 1) === '/') {
            $this->relativeDir = $relativeDir;
        } elseif (is_file($this->realDir . '/' . $relativeDir)) {
            $this->relativeDir = dirname($this->realDir . $relativeDir);
        } else {
            $this->relativeDir = $relativeDir . '/';
        }
        $this->realDir .= $this->relativeDir;
    }

    public function afficherRepertoire() {
        $this->parseDir();
        return array('folders' => $this->folders, 'files' => $this->files);
    }

    public function getUrls() {
        $this->parseDir();
        return $this->files;
    }

    public function getRelativePath() {
        return $this->relativeDir;
    }

    private function parseDir() {
        $fileList = array_diff(scandir($this->realDir), array('..', '.'));
        foreach ($fileList as $item) {
            if (is_dir($this->realDir . '/' . $item)) {
                $this->folders[] = array('url' => $this->relativeDir . $item, 'nom' => $item);
                if ($this->recursive) {
                    $oldRealDir = $this->realDir;
                    $oldRelativeDir = $this->relativeDir;
                    $this->realDir .= $item . '/';
                    $this->relativeDir .= $item . '/';
                    $this->parseDir();
                    $this->realDir = $oldRealDir;
                    $this->relativeDir = $oldRelativeDir;
                }
            } else {
                $this->files[] = array('url' => $this->relativeDir . $item
                    , 'directoryUrl' => $this->relativeDir
                    , 'nom' => $item);
            }
        }
    }

}
