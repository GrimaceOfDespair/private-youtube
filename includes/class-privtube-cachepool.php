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

class PrivTube_CachePool implements CacheItemPoolInterface {
  
  public function getItem($key) {
    
    return new PrivTube_CacheItem($key);
    
  }
  
  public function getItems(array $keys = array()) {
    
    $items = array();
    foreach ($keys as $key) {
        $item = $this->getItem($key);
        $items[$item->getKey()] = $item;
    }
    
    return new \ArrayIterator($items);    
  }

  public function hasItem($key) {
    
    return $this->getItem($key)->isHit();
    
  }
  
  public function clear() {
    
    global $wpdb;

    $options = $wpdb->options;

    $t = esc_sql( "_transient_timeout_" . PrivTube_CacheItem::$prefix );

    $sql = $wpdb->prepare (
      "
        SELECT option_name
        FROM $options
        WHERE option_name LIKE '%s'
      ",
      $t
    );

    $transients = $wpdb->get_col( $sql );

    // For each transient...
    foreach( $transients as $transient ) {

      // Strip away the WordPress prefix in order to arrive at the transient key.
      $key = str_replace( '_transient_timeout_', '', $transient );

      // Now that we have the key, use WordPress core to the delete the transient.
      delete_transient( $key );

    }
    
    // But guess what?  Sometimes transients are not in the DB, so we have to do this too:
    wp_cache_flush();
  }
  
  public function deleteItem($key) {
    
    return $this->getItem($key)->clear();
    
  }
  
  public function deleteItems(array $keys) {
    
    $items = array();
    $results = true;
    
    foreach ($keys as $key) {
      $results = $this->deleteItem($key) && $results;
    }
    
    return $results;
    
  }
  
  public function save(CacheItemInterface $item) {
    
    return $item->save();
    
  }
  
  public function saveDeferred(CacheItemInterface $item) {
    
    return $this->save($item);
    
  }

  public function commit() {
    
    return true;
    
  }
}
