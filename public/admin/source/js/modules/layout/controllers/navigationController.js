define(['./../module'], function (module) {

    module.registerController('NavigationController', ['$rootScope', '$scope', '$location', '$timeout', '$state', 'SessionService', 'orderDataService', 'USER_TYPES', 'AUTH_EVENTS', function ($rootScope, $scope, $location, $timeout, $state, SessionService, orderDataService, USER_TYPES, AUTH_EVENTS) {
            'use strict';
            
            $rootScope.$on(AUTH_EVENTS.Authenticated, function () {
                updateMenu();
                updateSelection();
            });
            
            $rootScope.$on(AUTH_EVENTS.LoginSuccess, function () {
                updateMenu();
                updateSelection();
            });

            function updateMenu() {
                $scope.session = SessionService.getSession();
                $scope.type = $scope.session.type ? USER_TYPES[$scope.session.type.toUpperCase()] : '';

                if ($scope.type === 'GROWSARI') {
                    $scope.menu = [{
                            label: 'Explore',
                            iconClasses: '',
                            separator: true
                        },
                        {
                            label: 'All Order',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/order/',
                        },
                        {
                            label: 'Manage Products',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/products/'
                        },
                        {
                            label: 'Manage Brands',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/brands/'
                        },
                        {
                            label: 'Manage Categories',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/categories/'
                        },
                        {
                            label: 'Manage Stores',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/stores/'
                        },
//                        {
//                            label: 'Manage Config',
//                            iconClasses: 'ti ti-view-list-alt',
//                            url: '#/gs/config/'
//                        },
                        {
                            label: 'Manage Sales Person',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/gs/salesperson/'
                        }
                    ];
                }

                if ($scope.type === 'WAREHOUSE') {
                    //updating count
                    $scope.base.getOrderCounts();

                    $scope.menu = [{
                            label: 'Explore',
                            iconClasses: '',
                            separator: true
                        }, {
                            label: 'New Orders',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/warehouse/new-order/',
                            type: 'new_orders_count'
                        }, {
                            label: 'Replacements',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/warehouse/replacement-order/',
                            type: 'replacement_confirmed_count'
                        }, {
                            label: 'Confirmed',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/warehouse/confirm-order/',
                            type: 'confirmed_orders_count'
                        }, {
                            label: 'Ready to pack',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/warehouse/pack-order/',
                            type: 'readytopack_orders_count'
                        }, {
                            label: 'All Orders',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/warehouse/order/'
                        }];
                }

                if ($scope.type === 'SHIPPER') {
                    //updating count
                    $scope.base.getOrderCounts();

                    $scope.menu = [{
                            label: 'Explore',
                            iconClasses: '',
                            separator: true
                        }, {
                            label: 'Manage Team',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/shipping/team/'
                        }, {
                            label: 'Confirmed Orders',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/shipping/confirmed-order/',
                            type: 'all_confirmed_orders_count'
                        }, {
                            label: 'Assign Shipper',
                            iconClasses: 'ti ti-view-list-alt',
                            type: 'assign_shipper_count',
                            children: [
                                {
                                    label: 'Map View',
                                    url: '#/shipping/assign-shipper/map/'
                                },
                                {
                                    label: 'List View',
                                    url: '#/shipping/assign-shipper/'
                                }
                            ]
                        }, {
                            label: 'Ready to Pick Up',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/shipping/packed/',
                            type: 'pickup_orders_count'
                        }, {
                            label: 'Dispatched',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/shipping/dispatched/',
                            type: 'dispatched_orders_count'
                        }, {
                            label: 'Delivered',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/shipping/delivered/'
                        }];
                }

                if ($scope.type === 'SALESPERSON') {
                    $scope.menu = [{
                            label: 'Explore',
                            iconClasses: '',
                            separator: true
                        }, {
                            label: 'Survey List',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/salesperson/survey/'
                        }];
                }
                
                if ($scope.type === 'CALLCENTER') {
                    $scope.base.getOrderCounts();
                    
                    $scope.menu = [{
                            label: 'Explore',
                            iconClasses: '',
                            separator: true
                        }, {
                            label: 'Orders',
                            iconClasses: 'ti ti-view-list-alt',
                            url: '#/callcenter/order/',
                            type: 'task_count'
                        }];
                }
                
            }

            var setParent = function (children, parent) {
                angular.forEach(children, function (child) {
                    child.parent = parent;
                    if (child.children !== undefined) {
                        setParent(child.children, child);
                    }
                });
            };

            $scope.findItemByUrl = function (children, url) {
                for (var i = 0, length = children.length; i < length; i++) {
                    if (children[i].url && children[i].url.replace('#', '') === url) {
                        return children[i];
                    }
                    if (children[i].children !== undefined) {
                        var item = $scope.findItemByUrl(children[i].children, url);
                        if (item) {
                            return item;
                        }
                    }
                }
            };

            setParent($scope.menu, null);

            $scope.openItems = [];
            $scope.selectedItems = [];
            $scope.selectedFromNavMenu = false;

            $scope.select = function (item) {
                // close open nodes
                if (item.open) {
                    item.open = false;
                    return;
                }
                for (var i = $scope.openItems.length - 1; i >= 0; i--) {
                    $scope.openItems[i].open = false;
                }
                $scope.openItems = [];
                var parentRef = item;
                while (typeof parentRef !== 'undefined' && parentRef !== null) {
                    parentRef.open = true;
                    $scope.openItems.push(parentRef);
                    parentRef = parentRef.parent;
                }

                // handle leaf nodes
                if (!item.children || (item.children && item.children.length < 1)) {
                    $scope.selectedFromNavMenu = true;
                    for (var j = $scope.selectedItems.length - 1; j >= 0; j--) {
                        $scope.selectedItems[j].selected = false;
                    }
                    $scope.selectedItems = [];
                    parentRef = item;
                    while (typeof parentRef !== 'undefined' && parentRef !== null) {
                        parentRef.selected = true;
                        $scope.selectedItems.push(parentRef);
                        parentRef = parentRef.parent;
                    }
                }
            };

            $scope.highlightedItems = [];
            var highlight = function (item) {
                var parentRef = item;
                while (typeof parentRef !== 'undefined' && parentRef !== null) {
                    if (parentRef.selected) {
                        parentRef = null;
                        continue;
                    }
                    parentRef.selected = true;
                    $scope.highlightedItems.push(parentRef);
                    parentRef = parentRef.parent;
                }
            };

            var highlightItems = function (children, query) {
                angular.forEach(children, function (child) {
                    if (child.label.toLowerCase().indexOf(query) > -1) {
                        highlight(child);
                    }
                    if (child.children !== undefined) {
                        highlightItems(child.children, query);
                    }
                });
            };

            // $scope.searchQuery = '';
            $scope.$watch('searchQuery', function (newVal, oldVal) {
                var currentPath = '#' + $location.path();
                if (newVal === '') {
                    for (var i = $scope.highlightedItems.length - 1; i >= 0; i--) {
                        if ($scope.selectedItems.indexOf($scope.highlightedItems[i]) < 0) {
                            if ($scope.highlightedItems[i] && $scope.highlightedItems[i] !== currentPath) {
                                $scope.highlightedItems[i].selected = false;
                            }
                        }
                    }
                    $scope.highlightedItems = [];
                } else
                if (newVal !== oldVal) {
                    for (var j = $scope.highlightedItems.length - 1; j >= 0; j--) {
                        if ($scope.selectedItems.indexOf($scope.highlightedItems[j]) < 0) {
                            $scope.highlightedItems[j].selected = false;
                        }
                    }
                    $scope.highlightedItems = [];
                    highlightItems($scope.menu, newVal.toLowerCase());
                }
            });

            $scope.$on('$routeChangeSuccess', updateSelection);
            
            function updateSelection() {
              if ($scope.selectedFromNavMenu === false) {
                    var item = $scope.findItemByUrl($scope.menu, $location.path());
                    if (item) {
                            $scope.select(item);
                    }
                }
                $scope.selectedFromNavMenu = false;
                $scope.searchQuery = '';
            }
        }]);

});