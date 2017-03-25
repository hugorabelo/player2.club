/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Feed', ['$http', function ($http) {
        var Feed = function (idUsuario, todos, idJogo) {
            this.items = [];
            this.ocupado = false;
            this.after = '0';
            this.idUsuario = idUsuario;
            this.todos = todos;
            this.idJogo = idJogo;
        };

        Feed.prototype.proximaPagina = function () {
            if (this.ocupado) {
                return;
            }
            this.ocupado = true;
            if (this.idJogo != undefined) {
                this.url = 'api/jogos/feed/' + this.idJogo + '/' + this.after + '/5';
            } else {
                this.url = 'api/usuario/feed/' + this.idUsuario + '/' + this.todos + '/' + this.after + '/5';
            }
            $http.get(this.url)
                .success(function (data) {
                    var items = data;
                    for (var i = 0; i < items.length; i++) {
                        this.items.push(items[i]);
                    }
                    if (this.after == this.items.length) {
                        this.ocupado = true;
                    } else {
                        this.after = this.items.length;
                        this.ocupado = false;
                    }
                }.bind(this));
        };

        return Feed;

    }]);
}());
