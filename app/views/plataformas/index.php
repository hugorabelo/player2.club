@extends('layouts.scaffold')

@section('main')

<div class="panel panel-primary" ng-app="liga">
	<div class="panel-heading">
		<h3 class="panel-title">TITULO</h3>
	</div>

	<table class="table" ng-controller="PlataformaController">
		<thead>
			<tr>
				<th>Descricao</th>
				<th>Logomarca</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
				<tr ng-repeat="plataforma in plataformas">
					<td>{{ plataforma.descricao }}</td>
					<td><img src="uploads/{{ plataforma.imagem_logomarca }}" height="40"/></td>
                    <td>

                    </td>
				</tr>
		</tbody>
	</table>

</div>

@stop

@section('custom_script')
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.3.3/angular.min.js"></script>

    <script type="text/javascript">
        var liga = angular.module('liga', []);

        liga.controller('PlataformaController', ['$scope', '$http', function ($scope, $http) {
            $scope.plataformas = {};

            $http.get('http://localhost/liga/public/plataformas/json').
            success(function (data) {
               $scope.plataformas = data.plataformas;
            });
        }]);
    </script>
@stop
