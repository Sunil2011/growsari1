/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define(['./../module'], function (module) {

    module.factory('pinesNotifications', ['$window', function ($window) {
            'use strict';
            return {
                notify: function (args) {
                    args.styling = 'fontawesome';
                    args.mouse_reset = false;
                    args.buttons = {
                        closer: true,
                        sticker: true
                    };
                    var notification = new $window.PNotify(args);
                    notification.notify = notification.update;
                    return notification;
                }
            };
        }])
        .service('notify', ['pinesNotifications', function (pinesNotifications) {
            'use strict';
            
            this.success = function(title, message, hide) {
                pinesNotifications.notify({
                    title: title,
                    text: message,
                    type: 'success',
                    hide: (typeof hide === 'undefined') ? true : hide
                });
            };
            
            this.error = function(title, message, hide) {
                pinesNotifications.notify({
                    title: title,
                    text: message,
                    type: 'error',
                    hide: (typeof hide === 'undefined') ? true : hide
                });
            };
            
            this.info = function(title, message, hide) {
                pinesNotifications.notify({
                    title: title,
                    text: message,
                    type: 'info',
                    hide: (typeof hide === 'undefined') ? true : hide
                });
            };
        }])
        .service('paginationSrvc', [function() {
            
            this.setPagination = function(totalCount,currentPage,numPerPage,callback) {
                this.totalItems = totalCount;
                this.currentPage = currentPage;
                this.numPerPage = numPerPage;
                this.maxSize = 5;
                this.callback = callback;
            };
        }]);
});
