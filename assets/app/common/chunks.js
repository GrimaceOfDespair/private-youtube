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
    .module('privtube.common')
    .filter('chunk', function() {

      function cacheIt(func) {
        var cache = {};
        return function(arg) {
          // if the function has been called with the argument
          // short circuit and use cached value, otherwise call the
          // cached function with the argument and save it to the cache as well then return
          return cache[arg] ? cache[arg] : cache[arg] = func(arg);
        };
      }

      // unchanged from your example apart from we are no longer directly returning this   ?
      function chunk(items, chunk_size) {
        var chunks = [];
        if (angular.isArray(items)) {
          if (isNaN(chunk_size))
            chunk_size = 4;
          for (var i = 0; i < items.length; i += chunk_size) {
            chunks.push(items.slice(i, i + chunk_size));
          }
        } else {
          console.log("items is not an array: " + angular.toJson(items));
        }
        return chunks;
      }
      // now we return the cached or memoized version of our chunk function
      // if you want to use lodash this is really easy since there is already a chunk and memoize function all above code would be removed
      // this return would simply be: return _.memoize(_.chunk);

      return cacheIt(chunk);

    });
    
})();