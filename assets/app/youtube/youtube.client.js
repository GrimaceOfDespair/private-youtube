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
    .factory('gapiClient', [
    
      '$q', 'configuration', '$window',
      function($q, configuration, $window) {
        
        var gapiClient = $window.gapi.client;
        
        return {
          loadChannel: function(channelId) {
            
            var deferred = $q.defer();
                
            gapiClient.load('youtube', 'v3', function() {
              gapiClient.youtube.search.list({
                part: 'snippet',
                channelId: configuration.channelId,
                maxResults: 50
              })
              .execute(function(response) {
                deferred.resolve(response.result);
              });
            });
            
            return deferred.promise;
          }
        };
      }]);
})();