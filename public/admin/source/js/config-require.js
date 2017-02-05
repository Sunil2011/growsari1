if (typeof define !== 'function') {
  // to be able to require file from node
  var define = require('amdefine')(module);
}

define({
  baseUrl: '.',
  // Here paths are set relative to `/source` folder
  paths: {
    'angular': 'vendor/angular/angular',
    'angular-resource': 'vendor/angular-resource/angular-resource',
    'angular-ui-router': 'vendor/angular-ui-router/release/angular-ui-router',
    "angular-bootstrap": "vendor/angular-bootstrap/ui-bootstrap-tpls.min",
    "angular-cookies": "vendor/angular-cookies/angular-cookies.min",
    "angular-sanitize": "vendor/angular-sanitize/angular-sanitize.min",
    "angular-animate": "vendor/angular-animate/angular-animate.min",
    "angular-couch-potato": "vendor/angular-couch-potato/dist/angular-couch-potato",
    "angular-loading-bar": "vendor/angular-loading-bar/build/loading-bar.min",
    'jquery': 'vendor/jquery/dist/jquery',
    "bootstrap": "vendor/bootstrap/dist/js/bootstrap.min",
    'async': 'vendor/requirejs-plugins/src/async',
    "domReady": "vendor/domReady/domReady",
    "jquery.ui.widget": "vendor/jquery-ui/ui/widget",
    "pnotify": "vendor/pnotify/dist/pnotify",
    "pnotify.main": "vendor/pnotify/libtests/browserify/index",
    "pnotify.animate": "vendor/pnotify/dist/pnotify.animate",
    "pnotify.buttons": "vendor/pnotify/dist/pnotify.buttons",
    "pnotify.nonblock": "vendor/pnotify/dist/pnotify.nonblock",
    "pnotify.desktop": "vendor/pnotify/dist/pnotify.desktop",
    "angular-google-maps": "vendor/angular-google-maps/dist/angular-google-maps",
    "angular-simple-logger": "vendor/angular-simple-logger/dist/angular-simple-logger",
    "lodash": "vendor/lodash/dist/lodash.min",
    "angular-xeditable": "vendor/angular-xeditable/dist/js/xeditable.min"
  }, 
  shim: {
    'angular': {'deps': ['jquery'], 'exports': 'angular'},
    "angular-animate": {"deps": ["angular"]},
    "angular-resource": {"deps": ["angular"]},
    "angular-cookies": {"deps": ["angular"]},
    "angular-sanitize": {"deps": ["angular"]},
    "angular-ui-router": {"deps": ["angular"]},
    "angular-bootstrap": {"deps": ["angular"]},
    "angular-couch-potato": {"deps": ["angular"]},
    "angular-loading-bar": {"deps": ["angular"]},
    "bootstrap": {"deps": ["jquery"]},
    "jquery.ui.widget": {"deps": ["jquery", "angular"]},
    "pnotify.main": {"deps": ["jquery","pnotify","pnotify.buttons","pnotify.animate","pnotify.desktop"]},
    "angular-google-maps": { "deps": ["angular", "angular-simple-logger", "lodash"] },
    "angular-simple-logger": { "deps": ["angular"] },
    "angular-xeditable": { "deps": ["angular"] }
  },
  "priority": [
    "jquery",
    "bootstrap",
    "angular"
  ]
});
