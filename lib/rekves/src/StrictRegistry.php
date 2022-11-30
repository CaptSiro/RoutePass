<?php

  abstract class StrictRegistry {
    private $__map = [];
    protected $useSerializedValues = false;
    public function enableSerializedValues () {
      $this->useSerializedValues = true;
    }
    public function disableSerializedValues () {
      $this->useSerializedValues = false;
    }

    private function optionallySerializeValue ($value) {
      return ($this->useSerializedValues == true)
        ? serialize($value)
        : $value;
    }
    private function optionallyUnserializeValue ($value) {
      return ($this->useSerializedValues == true)
        ? unserialize($value)
        : $value;
    }

    abstract protected function propNotFound ($propName);
    abstract protected function setValue ($propName, $value);



    public function get ($propName, $default = null) {
      if (isset($this->__map[$propName])) {
        return $this->optionallyUnserializeValue($this->__map[$propName]);
      }

      return (isset($default))
        ? $default
        : null;
    }


    public function load (&...$dictionaries) {
      foreach ($dictionaries as $dictionary) {
        foreach ($dictionary as $key => $value) {
          $this->__map[$key] = $value;
        }
      }
    }

    
    public function unset ($propName) {
      $this->setValue($propName, null);
      unset($this->__map[$propName]);
    }

    public function isset ($propName): bool {
      return isset($this->__map[$propName]);
    }


    public function __get ($propName) {
      $got = $this->get($propName);
      if ($got === null) {
        $this->propNotFound($propName);
      }

      return $this->optionallyUnserializeValue($got);
    }


    public function __set ($propName, $value) {
      $modified = $this->setValue($propName, $value);

      if ($modified !== null) {
        return $this->__map[$propName] = $this->optionallySerializeValue($modified);
      }

      return null;
    }





    public function stringify (): string {
      $rtn = "{";

      foreach ($this->__map as $key => $value) {
        $rtn .= "\n\t\"$key\": \"$value\",";
      }

      $rtn .= "\n}\n";

      return $rtn;
    }
    public function getMap (): array {
      return $this->__map;
    }
  }