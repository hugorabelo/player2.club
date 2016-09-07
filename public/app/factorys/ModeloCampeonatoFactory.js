AplicacaoLiga.factory('ModeloCampeonato', ['$http', function ($http) {
	return {
		getCriteriosClassificacao: function (id) {
			return $http.get('api/modeloCampeonato/getCriteriosClassificacao/' + id);
		}
	};
}])
