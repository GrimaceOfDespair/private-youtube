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