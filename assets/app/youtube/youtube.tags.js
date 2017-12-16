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
 */

(function () {
  'use strict';
  
  angular
    .module('privtube.youtube')
    .filter('filterByTag', function () {
      
      return function (items, tag) {
        
          if (!tag) {
            return items;
          }
          
          var filtered = []; // Put here only items that match
          (items || []).forEach(function (item) { // Check each item
          
              var matches = item.tags && item.tags.indexOf(tag) >= 0;
              if (matches) {           // If it matches
                  filtered.push(item); // put it into the `filtered` array
              }
          });
          return filtered; // Return the array with items that match any tag
      };
  });
})();