<?php
  
  class File {
    public $name, $ext, $fullName, $type, $temporaryName, $error, $size;
    
    public function __construct ($file) {
      $this->fullName = $file["name"];
      [$name, $ext] = $this->getExt();
      $this->name = $name;
      $this->ext = $ext;
      $this->type = $file["type"];
      $this->temporaryName = $file["tmp_name"];
      $this->error = $file["error"];
      $this->size = $file["size"];
    }
    
    public function move (string $destination) {
      move_uploaded_file($this->temporaryName, $destination);
    }
    
    public function getExt (): array {
      $index = 0;
      $len = strlen($this->fullName);
  
      for ($i = ($len - 1); $i >= 0; $i--) {
        if ($this->fullName[$i] == ".") {
          $index = $i;
          break;
        }
      }
  
      $end = ($index == $len) ? $index : $index + 1;
  
      return [substr($this->fullName, 0, $index), substr($this->fullName, $end)];
    }
  }