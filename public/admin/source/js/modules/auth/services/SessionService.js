define(['./../module'], function (module) {

  module.service('SessionService', ['$q', '$log', SessionService]);

  function SessionService($q, $log) {
    this.id = null;
    this.account = null;
    this.type = null;
    this.role = null;
    
    this.create = function (id, account) {
      this.id = id;
      this.account = account;
      this.type = account.type;
      this.role = account.role;
    };
    
    this.destroy = function () {
      this.id = null;
      this.account = null;
      this.type = null;
      this.role = null;
    };
    
    this.getSession = function () {
      return {
        id: this.id,
        account: this.account,
        type: this.type,
        role: this.role
      };
    };    
  }

});