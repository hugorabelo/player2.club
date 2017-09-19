/*global angular */
(function () {
    'use strict';
    angular.module('player2').factory('Feed', ['$http', function ($http) {
        var Feed = function (idUsuario, todos, idJogo, idEquipe) {
            this.items = [];
            this.ocupado = false;
            this.after = '0';
            this.idUsuario = idUsuario;
            this.todos = todos;
            this.idJogo = idJogo;
            this.idEquipe = idEquipe;
            this.partidasRegistradas = [];
            this.itemsRegistrados = [];
            this.totalAtividades = 0;
        };

        Feed.prototype.proximaPagina = function () {
            if (this.ocupado) {
                return;
            }
            this.ocupado = true;
            if (this.idJogo != undefined) {
                this.url = 'api/jogos/feed/' + this.idJogo + '/' + this.after + '/5';
            } else if (this.idEquipe != undefined) {
                this.url = 'api/equipe/feed/' + this.idEquipe + '/' + this.after + '/5';
            } else {
                this.url = 'api/usuario/feed/' + this.idUsuario + '/' + this.todos + '/' + this.after + '/5';
            }

            $http.get(this.url)
                .success(function (data) {
                    var items = data;
                    this.totalAtividades += items.length;
                    for (var i = 0; i < items.length; i++) {
                        var item = items[i];
                        if (item.partidas_id != null) {
                            if ((this.partidasRegistradas.indexOf(item.partidas_id) < 0) && (this.itemsRegistrados.indexOf(item.id) < 0)) {
                                this.partidasRegistradas.push(item.partidas_id)

                                var outrosUsuarios = [];
                                var vm = this;
                                angular.forEach(item.objeto.usuarios, function (usuario) {
                                    if (usuario.users_id != item.users_id) {
                                        this.push(usuario);
                                    }
                                }, outrosUsuarios);
                                item.outros_usuarios = outrosUsuarios;

                                this.items.push(item);
                                this.itemsRegistrados.push(item.id);
                            }
                        } else {
                            if (this.itemsRegistrados.indexOf(item.id) < 0) {
                                this.items.push(item);
                                this.itemsRegistrados.push(item.id);
                            }
                        }
                    }
                    if (this.after == this.totalAtividades) {
                        this.ocupado = true;
                    } else {
                        this.after = this.totalAtividades;
                        this.ocupado = false;
                    }
                }.bind(this));
        };

        return Feed;

    }]);
}());
