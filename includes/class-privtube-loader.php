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

/**
 * PrivTube actions and filters
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube_Loader {

  protected $actions;

  protected $filters;

  protected $shortcodes;

  public function __construct() {
    
    $this->actions = array();
    $this->filters = array();
    $this->shortcodes = array();
    
  }

  public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
    
    $this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
    
  }

  public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
    
    $this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
    
  }

  public function add_shortcode( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
    
    $this->shortcodes = $this->add( $this->shortcodes, $hook, $component, $callback, $priority, $accepted_args );
    
  }

  private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {

    $hooks[] = array(
      'hook'          => $hook,
      'component'     => $component,
      'callback'      => $callback,
      'priority'      => $priority,
      'accepted_args' => $accepted_args
    );

    return $hooks;

  }

  public function run() {

    foreach ( $this->filters as $hook ) {
      add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
    }

    foreach ( $this->actions as $hook ) {
      add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
    }

    foreach ( $this->shortcodes as $hook ) {
      add_shortcode( $hook['hook'], array( $hook['component'], $hook['callback'] ) );
    }

  }

}
