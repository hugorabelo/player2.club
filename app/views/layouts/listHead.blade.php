<nav class="navbar navbar-default" role="navigation">
	<div class="container-fluid">
		<div class="navbar-header">
			<p class="navbar-brand">{{ $titulo }}</p>
		</div>
		<div class="collapse navbar-collapse">
			{{ link_to_route($link, $mensagem, null, array('class' => 'btn btn-success navbar-btn')) }}
			<ul class="nav navbar-nav navbar-right">
				<li><a href="">12</a></li>
				<li><a href="">11</a></li>
				<li><a href="">33</a></li>
			</ul>
		</div>
	</div>
</nav>
