<html>
	<head>
		<?php root()->themes->cp_head() ?>
	</head>
	<body class="login">
		<div class="container">
			<div class="row">
				<div class="col-sm-4 col-sm-offset-4">
					<div class="notice">
						<? root()->hooks->notice->get('login') ?>
					</div>
					<form class="form" method="post">
						<div class="form-group">
							<label for="username">User Name</label>
							<input class="form-control" type="text" name="username" />
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input class="form-control" type="password" name="password" />
						</div>
						<input class="btn btn-primary btn-block" type="submit" name="submit" />
					</form>
				</div>
			</div>
		</div>
	</body>
</html>