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