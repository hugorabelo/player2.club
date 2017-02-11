angular.module('player2').factory('Permissao', ['$http', function ($http) {
    return {
        get: function (usuario_tipos_id) {
            return $http.get('api/permissao/' + usuario_tipos_id);
        },

        save: function (permissao) {
            return $http({
                method: 'POST',
                url: 'api/permissao',
                data: $.param(permissao),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            });
        },

        destroy: function (id) {
            return $http.delete('api/permissao/' + id);
        },

        reportarBug: function (feedback) {
            console.log(feedback);
            return $http({
                method: 'POST',
                url: 'api/permissao/bugReport',
                headers: {
                    'Content-Type': undefined
                },
                transformRequest: function (data) {
                    var formData = new FormData();
                    angular.forEach(feedback, function (value, key) {
                        formData.append(key, value);
                    });
                    angular.forEach(feedback.files, function (obj) {
                        formData.append('files[]', obj.lfFile);
                    });
                    return formData;
                }
            });
        }
    }
}]);
