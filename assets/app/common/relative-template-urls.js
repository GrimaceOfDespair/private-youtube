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