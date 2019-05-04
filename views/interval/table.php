<?php foreach ($intervals as $interval): ?>
	<div class="form-group">
		<?= $app->view->make('interval/one', ['interval' => $interval]) ?>
	</div>
<?php endforeach; ?>
