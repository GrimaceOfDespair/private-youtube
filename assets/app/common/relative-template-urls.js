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

(function(){
  angular
    .module('privtube.common')
    
    // Load templates relatively to app root
    .config(['$provide',function($provide) {
        
      return $provide.decorator("$http", ["$delegate", "configuration", function($delegate, configuration) {
        var get = $delegate.get;
        $delegate.get = function(url, config) {
          // Check is to avoid breaking AngularUI ui-bootstrap-tpls.js: "template/accordion/accordion-group.html"
          if (url.indexOf('calendar') === 0) {
            url = decorateUrl(url, configuration, 'calendar/');
          } else if (!~url.indexOf('template/') && !~url.indexOf('directives/')) {
            url = decorateUrl(url, configuration);
          }
          return get(url, config);
        };
        return $delegate;
      }]);
      
      function decorateUrl(url, configuration, rebase) {
        // Append ?v=[cacheBustVersion] to url
        url += (url.indexOf("?") === -1 ? "?" : "&");
        url += "v=" + configuration.version;
        if (url.slice(0, 1) != '/') {
            url = configuration.templateBaseUrl + 'app/' + (rebase || '') + url;
        }
        
        return url;
      }
    }]);
})();