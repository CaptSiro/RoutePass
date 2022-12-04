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
      return $this->useSerializedValues
        ? serialize($value)
        : $value;
    }
    private function optionallyDeserializeValue ($value) {
      return $this->useSerializedValues
        ? unserialize($value)
        : $value;
    }
  
    abstract protected function propNotFound ($propertyName);
    abstract protected function setValue ($propertyName, $value);
  
    public function looselyGet ($propertyName, $default = null) {
      if (isset($this->__map[$propertyName])) {
        return $this->optionallyDeserializeValue($this->__map[$propertyName]);
      }
    
      return $default;
    }
    public function get ($propertyName) {
      if (!$this->isset($propertyName)) {
        $this->propNotFound($propertyName);
      }
  
      return $this->optionallyDeserializeValue($this->__map[$propertyName]);
    }
    
    public function set ($propertyName, $value) {
      $modified = $this->setValue($propertyName, $value);
  
      if ($modified !== null) {
        return $this->__map[$propertyName] = $this->optionallySerializeValue($modified);
      }
  
      return null;
    }
    public function modify ($propertyName, Closure $modifier) {
      $this->__map[$propertyName] = $modifier($this->__map[$propertyName]);
    }
    
    public function isset ($propertyName): bool {
      return isset($this->__map[$propertyName]);
    }
  
    public function stringify (): string {
      $return = "{";
    
      foreach ($this->__map as $key => $value) {
        $return .= "\n\t\"$key\": \"$value\",";
      }
    
      $return .= "\n}\n";
    
      return $return;
    }
    public function getMap (): array {
      return $this->__map;
    }
    public function load (array ...$dictionaries) {
      foreach ($dictionaries as $dictionary) {
        foreach ($dictionary as $name => $value) {
          $this->__map[$name] = $value;
        }
      }
    }
  }