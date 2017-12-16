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
 * PrivTube assets
 *
 * @package    BitHive
 * @author     Igor Kalders <igor@bithive.be>
 */
class PrivTube_Assets {
  
  private $assets;
  
  private $manifest;
  
  private $plugin_dir_path;
  
  private $is_development;
  
  public function __construct($is_development) {
    
    $this->plugin_dir_path = plugin_dir_path( dirname(__FILE__) );
    
    $this->is_development = $is_development;
    
    if ($this->is_development) {
      
      $manifest_path = $this->plugin_dir_path . 'assets/manifest.json';
    
      if (file_exists($manifest_path)) {
        $manifest = json_decode(file_get_contents($manifest_path), true);
        $this->manifest = $manifest['dependencies'];
      }
    }
    
    $assets_path = $this->plugin_dir_path . trim(DIST_DIR, '/') . '/assets.json';
    
    if (file_exists($assets_path)) {
      $this->assets = json_decode(file_get_contents($assets_path), true);
    } else {
      $this->assets = [];
    }
  }

  function get_path($filename) {
    $dist_path = plugins_url( DIST_DIR, dirname(__FILE__) );
    $path = $dist_path . dirname($filename) . '/';
    $file = basename($filename);

    if (array_key_exists($file, $this->assets)) {
      return $path . $this->assets[$file];
    } else {
      return $path . $file;
    }
  }
  
  function get_paths($script) {
    
    $paths = array();
    
    $plugin_base = dirname(__file__);
    $plugin_dir_path = $this->plugin_dir_path;
    
    if (
      $this->is_development &&
      ($dependencies = $this->manifest) &&
      ($files = $dependencies[basename($script)]) &&
      ($script_dependencies = $files['files'])) {
        
      $extensions = array(pathinfo($script, PATHINFO_EXTENSION));
        
      $bower_dependencies = $this->get_bower_dependencies($extensions);

      $paths = array();
      
      // Bower dependencies
      foreach ( $this->sort_dependencies($bower_dependencies) as $bower_dependency ) {
        $module_name = $bower_dependency['name'];
        $module_version = $bower_dependency['version'];
        
        // Create path entry for every file
        if (isset($bower_dependency['files'])) {
          foreach ( $bower_dependency['files'] as $script_file ) {
            $asset_path = substr( $script_file, strlen($plugin_dir_path) );
            $plugin_url = plugins_url( $asset_path, $plugin_base );
            $version = $module_version . '.' . filemtime($script_file);
            $paths [] = (object)array(
              'name' => $module_name,
              'path' => $asset_path,
              'url' => $plugin_url,
              'version' => $version);
          }
        }
      }
      
      $script_path = 'assets/' . $script;
      
      foreach ( $script_dependencies as $script_dependency ) {
        
        // Own dependencies
        foreach ( $this->glob( $plugin_dir_path . 'assets/' . $script_dependency ) as $script_file ) {
          
          $asset_path = substr( $script_file, strlen($plugin_dir_path) );
          $plugin_url = plugins_url( $asset_path, $plugin_base );
          $version = filemtime($script_file);
          $path = (object)array(
            'name' => 'main',
            'path' => $asset_path,
            'url' => $plugin_url,
            'version' => $version);
              
          if ( $asset_path ==  $script_path ) {
            $root = $path;
          } else {
            $paths []= $path;
          }
        }
      }
    }
    
    if (!$root) {
      $root = (object)array(
        'name' => 'main',
        'path' => '',
        'url' => $this->get_path($script),
        'version' => '' . microtime());
    }
    
    return (object)array(
      'root' => $root,
      'dependencies' => $paths
    );
  }
  
  private function get_bower_dependencies($extensions, $bower_module = null, $bower_dependencies = null) {
      
    $bower_module_base = $this->plugin_dir_path;
    if ($bower_module) {
      $bower_module_base .= 'bower_components/'. $bower_module . '/';
    }
    $bower_path = $bower_module_base . 'bower.json';
    
    if (!$bower_dependencies) {
      $bower_dependencies = array();
    }
  
    // Analyze the bower.js file for the current module
    $bower_module_dependencies = array();
    
    $version = '0';
    
    if (!file_exists($bower_path)) {
      
      $bower_path = $bower_module_base . '.bower.json';
      if (file_exists($bower_path)) {
        
        $bower = json_decode(file_get_contents($bower_path), true);
        if (isset($bower['version'])) {
          $version = $bower['version'];
        }
      }
      
      // Todo: figure out what to do with bower modules
      // that don't specify a dependency
      $bower_module_files = array();
      
    } else {
      
      $bower = json_decode(file_get_contents($bower_path), true);
      
      if (isset($bower['dependencies'])) {
        
        $bower_module_dependencies = $bower['dependencies'];
      
        if (!is_array($bower_module_dependencies)) {
          $bower_module_dependencies = array($bower_module_dependencies);
        }
          
        foreach ($bower_module_dependencies as $bower_module_dependency => $bower_module_version) {
          if (!isset($bower_dependencies[$bower_module_dependency])) {
            $bower_dependencies = $this->get_bower_dependencies($extensions, $bower_module_dependency, $bower_dependencies);
          }
        }
      }
      
      // Get all files this module depends on
      if (isset($bower['main'])) {
        
        $main = $bower['main'];
        if (!is_array($main)) {
          $main = array($main);
        }
        
        $bower_module_files = array();
        foreach ($main as $main_pattern) {
          $main_files = $this->glob($bower_module_base . $main_pattern);
          foreach ($main_files as $main_file) {
            if (in_array( pathinfo($main_file, PATHINFO_EXTENSION), $extensions)) {
              $bower_module_files []= realpath($main_file);
            }
          }
        }
      } else {
        $bower_module_files = array ( $bower_module_base . $bower_module . '.json' );
      }
      
      if (isset($bower['version'])) {
        $version = $bower['version'];
      }
    }
    
    $bower_dependencies[$bower_module] = array(
      'name' => $bower_module,
      'version' => $version,
      'files' => $bower_module_files,
      'dependencies' => $bower_module_dependencies
    );
    
    return $bower_dependencies;
  }
  
  private function sort_dependencies($bower_dependencies) {
    $sorted_dependencies = array();
    
    foreach ( $bower_dependencies as $bower_module => $bower_module_data ) {
      
      // Add dependencies of this module
      if (isset($bower_module_data['dependencies'])) {
        foreach ( $bower_module_data['dependencies'] as $dependent_module => $dependent_version) {
          if (!in_array($dependent_module, $sorted_dependencies)) {
            array_unshift($sorted_dependencies, $dependent_module);
          }
        }
      }
      
      // Add this module (unless main)
      if ($bower_module && !in_array($bower_module, $sorted_dependencies)) {
        $sorted_dependencies []= $bower_module;
      }
    }
    
    $sorted_bower_modules = array();
    foreach ( $sorted_dependencies as $bower_module ) {
      $sorted_bower_modules []= $bower_dependencies[$bower_module];
    }
    
    return $sorted_bower_modules;
  }
  
  public function glob($pattern) {
    
    // if not using ** then just use PHP's glob()
    if (stripos($pattern, '**') === false) {
      return glob($pattern);
    }

    $patterns = array();

    $files = array();

    $double_star = stripos($pattern, '**');

    $basePattern = rtrim(substr($pattern, 0, $double_star), '/');
    $rootPattern = $basePattern .'/*';
    $restPattern = substr($pattern, $double_star + 2);

    // Add pattern to find files in current folder
    $patterns[] = $basePattern . $restPattern;
    
    // Add patterns to search subfolders
    while($dirs = glob($rootPattern, GLOB_ONLYDIR)) {
      $rootPattern = $rootPattern .'/*';

      foreach($dirs as $dir) {
        $patterns[] = $dir . $restPattern;
      }
    }

    // Collect files and subfolders
    foreach($patterns as $pattern) {
      $files = array_merge($files, $this->glob($pattern, $flags));
    }

    return array_unique($files);
  }
}
