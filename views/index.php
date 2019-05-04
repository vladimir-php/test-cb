<?= $app->view->make('layout/header') ?>

<div class="starter-template">

	<div id="interval_widget"></div>

</div>


<?php ob_start(); // @todo move this logic to special code in the View class ?>
<script src="/js/interval.js" type="application/javascript"></script>
<script type="application/javascript">

	// @todo move this templates to vue js or another specified js template engine
	let interval_widget = new IntervalWidget(
		$('#interval_widget'),
		'<?=$app->view->make('interval/js/one')->toJs()?>',
		'<?=$app->view->make('interval/js/form')->toJs()?>'
	);
	interval_widget.addItemList(<?=json_encode($intervals)?>);
	interval_widget.refreshResult();

</script>
<?php $scripts = ob_get_clean(); ?>


<?= $app->view->make('layout/footer', ['scripts' => $scripts]) ?>
