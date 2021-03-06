<?php 
/*
 * Copyright Header - A WordPress plugin to list YouTube videos
 * Copyright (C) 2016-2017 Igor Kalders <igor@bithive.be>
 *
 * This file is part of Copyright Header.
 *
 * Copyright Header is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Copyright Header is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Copyright Header.  If not, see <http://www.gnu.org/licenses/>.
 */ ?>
<?php
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\CacheItemInterface;

class PrivTube_CacheItem implements CacheItemInterface {
  
  public static $prefix = 'pt_';
  
  protected $data;
  
  protected $expiration;
  
  protected $key;
  
  protected $isHit = null;
  
  public function __construct( $key ) {
    
    $this->key = $key;
    
  }
  
  public function getKey() {
    
    return $this->key;
    
  }
  
  public function get() {
    
    if (!isset($this->data)) {
      
      $this->data = $this->executeGet();
    }
    
    return $this->data;
  }
  
  public function isHit() {
    
    return !$this->isMiss();
       
  }
  
  public function set($value) {
    
    if (!isset($this->key)) {
        return false;
    }
    $this->data = $value;
    return $this;
  }
  
  public function expiresAt($expiration) {
    
    if (!is_null($expiration) && !($expiration instanceof \DateTimeInterface)) {
        # For compatbility with PHP 5.4 we also allow inheriting from the DateTime object.
        if (!($expiration instanceof \DateTime)) {
            throw new InvalidArgumentException('expiresAt requires \DateTimeInterface or null');
        }
    }
    
    $this->expiration = $expiration;
    return $this;
  }
  
  public function expiresAfter($time) {
    
    $date = new \DateTime();
    if (is_numeric($time)) {
      
      $dateInterval = \DateInterval::createFromDateString(abs($time) . ' seconds');
      if ($time > 0) {
        $date->add($dateInterval);
      } else {
        $date->sub($dateInterval);
      }
      
      $this->expiration = $date;
        
    } elseif ($time instanceof \DateInterval) {
      
      $date->add($time);
      $this->expiration = $date;
        
    }
    
    return $this;
  }
  
  public function save() {
    
    $time = $this->expiration;
    
    if (!isset($this->key)) {
        return false;
    }

    if (isset($time) && ($time instanceof \DateTime)) {
      $cacheTime = $time->getTimestamp() - time();
    } else {
      $cacheTime = 60 * 60 * 24; // one day
    }

    set_transient(PrivTube_CacheItem::$prefix . $this->key, $this->data, $cacheTime);
  }
  
  public function clear() {
    
    unset($this->data);
    unset($this->expiration);

    if (isset($this->key)) {
      return delete_transient(PrivTube_CacheItem::$prefix . $this->key);
    }
    
    return false;
  }
  
  public function isMiss()
  {
      if (!isset($this->isHit)) {
        $this->get();
      }
      
      return !$this->isHit;
  }
  
  private function executeGet()
  {
    $this->isHit = false;
    
    if (!isset($this->key)) {
        return null;
    }
    
    if (false !== ($data = get_transient(PrivTube_CacheItem::$prefix . $this->key))) {
      $this->isHit = true;
    
      return $data;
    }
    
    return null;
  }
}

