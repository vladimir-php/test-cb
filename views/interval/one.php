<div class="col-md-4">
	<input type="date" class="form-control" aria-describedby="Date Start" placeholder="Enter date start" value="<?=isset($interval) ? $interval->date_start : ''?>" />
</div>
<div class="col-md-4">
	<input type="date" class="form-control" aria-describedby="Date End" placeholder="Enter date end" value="<?=isset($interval) ? $interval->date_end : ''?>" />
</div>
<div class="col-md-4">
	<input type="number" class="form-control" aria-describedby="Price" placeholder="Enter price" value="<?=isset($interval) ? $interval->price: ''?>" />
</div><br /><br />

