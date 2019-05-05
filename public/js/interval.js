'use strict';

class IntervalWidget {

	constructor(root_box, template_item, template_form) {

		this.items = {};
	 	this.edit_item = null;

		this.root_box = root_box;
		this.template_item = template_item;
		this.template_form = template_form;

		// --- BEGIN: Form create
		this.form_create = new IntervalForm(this, new IntervalItem(this, {
			date_start: "2010-01-01",
			date_end: "2010-02-02",
			price: 45.456
		}), [
			{
				title: 'Create',
				action: () => {
					$.ajax({
						url: '/interval',
						type: 'PUT',
						data: JSON.stringify(this.form_create.getData()),
						contentType: 'application/json',
						dataType: 'json',
						success: (result) => {
							this.form_create.clear();
							this.addItem(result);

							this.refreshResult();
						}
					});
				}
			},
			{
				title: 'Delete All',
				action: () => {
					if (confirm('Are you sure you want to delete all intervals?') ) {
						$.ajax({
							url: '/interval/all',
							type: 'DELETE',
							dataType: 'json',
							success: (result) => {

								// Remove all items
								for (var i in this.items) {
									this.removeItem(i)
								}

								this.refreshResult();
							}
						});
					}
				}
			}
		]);

		// Render a create form
		this.root_box
			.append(this.form_create.box);
		this.form_create.box
			.after('<br /><br />');

		// --- END: Form create



		// --- BEGIN: Result
		this.result = new IntervalResult(this);
		this.root_box
			.append(this.result.box);
		this.result.box
			.after('<br /><br />');
		// --- END: Result
	}


	getSortedItems (sort_key, reverse) {
		let keys = []; let sorted_items = {};
		for (let i in this.items) {
			sorted_items[this.items[i].sort_key] = this.items[i];
			keys.push(this.items[i].sort_key);
		}
		keys.sort();

		if (reverse) {
			keys = keys.reverse();
		}


		let result = {};
		for (let key of keys) {
			result[key] = sorted_items[key];
		}
		return result;
	}


	setEditItem (item) {
		if (this.edit_item) {
			this.edit_item.switchTo('info');
		}
		if (item) {
			this.edit_item = item;
			this.edit_item.switchTo('form');
		}
	}


	addItemList (items) {
		for (let i in items) {
			this.addItem(items[i]);
		}
	}

	addItem (data) {
		var item = new IntervalItem(this, data);

		// Find a next item: before it the current item will be pushed
		// Items is with the ID sorting by default, we need to sort by date_start
		var next_item = null; let d_min = 0;
		for (let i in this.items) {
			let new_d_min = this.items[i].sort - item.sort;
			if (new_d_min > 0) {
				if (!d_min) {
					d_min = new_d_min;
				}
				if (new_d_min <= d_min) {
					d_min = new_d_min;
					next_item = this.items[i];
				}
			}
		}
		if (next_item) {
			next_item.box.before(item.box);
		}
		else {
			this.root_box.append(item.box);
		}

		// Add item to the array
		this.items[data.id] = item;
	}

	removeItem (id) {
		this.items[id].box.remove();
		delete this.items[id];
	}


	refreshResult () {
		this.result.refresh();
	}


	refreshAll () {

		// Clear all html
		this.root_box.html('');

		// Form create intialization
		this.form_create.initialize();

		// All items initialization
		for (let i in this.items) {
			this.items[i].initialize();
		}

		// Render all
		this.render();
	}


}


class IntervalItem {

	constructor (widget, data) {
		this.widget = widget;
		this.data = data;

		// Create date objects
		this.date_start = new Date(data.date_start);
		this.date_end = new Date(data.date_end);

		// Start & end timestamps
		this.ts_start = this.date_start.getTime() / 1000;
		//this.ts_end = this.date_end.getTime() / 1000;

		// Sort value
		this.sort = this.ts_start; //(this.ts_start +'.'+ this.data.id)*1;

		this.initialize();
	}


	initialize () {
		this.info_box = $('<div />')
			.append(this.render() );

		this.form = new IntervalForm(this.widget, this, [
			{title: 'Update', action: () => { this.edit(); }},
			{title: 'Delete', action: () => { this.delete(); }},
			{title: 'Cancel', action: () => { this.switchTo('info'); return false; }}
		]);

		this.box = $('<div />')
			.addClass('interval-item')
			.append(this.info_box)
			.append(this.form.box);
		this.form.hide();

		this.bindEvents();
	}

	bindEvents () {
		this.box.bind('click', () => {
			this.widget.setEditItem (this)
		});
	}


	switchTo (mode) {
		switch (mode) {
			case 'info':
				this.info_box.show();
				this.form.hide();
				break;
			case 'form':
				this.info_box.hide();
				this.form.show();
				break;
		}
	}

	replaceData (template) {
		return template.replace(/{date_start}|{date_end}|{price}/gi, (key) => {
			key = key.replace(/{|}/gi, '');
			if (!this.data[key]) {
				return '';
			}
			return this.data[key];
		});
	}

	render () {
		return this.replaceData(this.widget.template_item);
	}


	edit () {
		let data = this.form.getData();
		data.id = this.data.id;
		$.ajax({
			url: '/interval',
			type: 'POST',
			data: data,
			dataType: 'json',
			success: (result) => {

				// Update an item data
				this.data = data;

				// @todo optimize this code to only re-init call
				this.widget.removeItem(this.data.id);
				this.widget.addItem(this.data);

				// Refresh result
				this.widget.refreshResult();
			}
		});
	}

	delete () {
		if (confirm('Are you sure you want to delete this interval?') ) {
			$.ajax({
				url: '/interval',
				type: 'DELETE',
				data: {id: this.data.id},
				dataType: 'json',
				success: (result) => {
					this.widget.removeItem(this.data.id);

					// Refresh result
					this.refreshResult();
				}
			});
		}
	}

}


class IntervalForm {

	constructor (widget, item, buttons) {
		this.widget = widget;
		this.item = item;
		this.buttons = buttons;

		this.initialize();
	}

	initialize () {

		// Add a base template
		this.box = $('<div />')
			.addClass('interval-form')
			.append(this.render() );

		// Add buttons
		for (let i in this.buttons) {
			let button = $('<button />')
				.addClass('btn btn-primary')
				.html(this.buttons[i].title)
				.bind('click', this.buttons[i].action);
			this.box.find('.buttons')
				.append(button)
				.append('&nbsp;&nbsp;');
		}
	}

	getData () {
		return {
			date_start: this.box.find('[name=date_start]').val(),
			date_end: this.box.find('[name=date_end]').val(),
			price: this.box.find('[name=price]').val(),
		};
	}

	clear () {
		this.box.find('[name=date_start]').val('');
		this.box.find('[name=date_end]').val('');
		this.box.find('[name=price]').val('');
	}

	show () {
		this.box.show();
	}

	hide () {
		this.box.hide();
	}

	render () {
		return this.item.replaceData(this.widget.template_form);
	}

}


class IntervalResult {

	constructor (widget) {
		this.widget = widget;
		this.initialize();
	}

	initialize () {
		this.box = $('<div />');
		this.result = [];
	}


	removeInterval (list, interval) {
		for (let i in list) {
			if (list[i].isEqual(interval) ) {
				delete list[i];
			}
		}
		return list;
	}


	addInterval (list, new_interval) {
		let new_list = [];


		// --- BEGIN: merge intervals
		for (let i in list) {
			let interval = list[i];

			// Merge intervals to a new one
			if (new_interval.isCompositePart(interval) ||
				new_interval.hasIntersection(interval) && new_interval.price === interval.price
			) {
				new_interval = new_interval.merge(interval);
				new_list.push(new_interval);
			}
			else {
				new_list.push(interval);
			}
		}
		this.debugOutput(new_list);
		list = new_list; new_list = []; // List overriding
		// END: merge intervals



		// --- BEGIN: Split intervals
		let new_is_added = false;
		for (let i in list) {
			let interval = list[i];

			// Has absorption?
			if (new_interval.hasAbsorption(interval) ) {
				continue;
			}

			// Has not intersection?
			if (!new_interval.hasIntersection(interval)) {
				new_list.push(interval);

			// Split intervals
			} else {

				// Has date_start intersection
				if (new_interval.hasStartIntersection(interval)) {
					new_list.push(new IntervalResultItem(
						interval.date_start,
						new_interval.dateStartBefore(),
						interval.price
					));
				}

				// More priority interval
				if (!new_is_added) { // Prevent to duplicate interval whan it have intersections with two other intervals
					new_list.push(new_interval);
				}

				// Has date_end intersection
				if (new_interval.hasEndIntersection(interval)) {
					new_list.push(new IntervalResultItem(
						new_interval.dateEndAfter(),
						interval.date_end,
						interval.price
					));
				}

				new_is_added = true;
			}

			// Delete old objects
			// @todo maybe easy to work only with structure instead an IntervalResultItem object
			delete list[i];
		}
		// --- END: Split intervals


		// Need to add a new interval?
		if (!new_is_added) {
			new_list.push(new_interval);
		}

		return new_list;
	}

	clear () {
		this.box.html('');
	}

	refresh () {

		this.clear();

		let items = []; let list = [];
		for (let i in this.widget.items) {
			let item = this.widget.items[i];
			list = this.addInterval (list,
				new IntervalResultItem(item.date_start, item.date_end, item.data.price)
			);
		}

		for (var interval of list) {
			this.box.append(
				$('<div />').html(
					interval.render()
				)
			);
		}
	}

	debugOutput (list) {
		console.log('----');
		for (let item of list) {
			console.log(item.render());
		}
	}

}


class IntervalResultItem {

	constructor (date_start, date_end, price) {
		this.date_start = date_start;
		this.date_end = date_end;
		this.price = price;

		// Timestamps
		this.ts_start = this.date_start.getTime();
		this.ts_end = this.date_end.getTime();
	}

	dateStartBefore () {
		var new_date = new Date(this.ts_start);
		new_date.setDate(new_date.getDate() - 1);
		return new_date;
	}

	dateEndAfter () {
		var new_date = new Date(this.ts_end);
		new_date.setDate(new_date.getDate() + 1);
		return new_date;
	}

	hasAbsorption (interval) {
		return (
			this.ts_start <= interval.ts_start &&
			this.ts_end >= interval.ts_end
		);
	}

	hasIntersection (interval) {
		return (
			this.hasStartIntersection(interval) ||
			this.hasEndIntersection(interval)
		);
	}

	hasStartIntersection (interval) {
		return (
			this.ts_start > interval.ts_start &&
			this.ts_start <= interval.ts_end
		);
	}

	hasEndIntersection (interval) {
		return (
			this.ts_end > interval.ts_start &&
			this.ts_end <= interval.ts_end
		);
	}

	minDateStart (interval) {
		return this.ts_start < interval.ts_start ? this.date_start : interval.date_start;
	}

	maxDateEnd (interval) {
		return this.ts_end > interval.ts_end ? this.date_end : interval.date_end;
	}

	isCompositePart (interval) {
		if (this.price !== interval.price) {
			return false;
		}
		if (this.dateStartBefore().getTime() === interval.ts_end ||
			this.dateEndAfter().getTime() === interval.ts_start
		) {
			return true;
		}
	}

	merge (interval) {
		return new IntervalResultItem(
			this.minDateStart(interval),
			this.maxDateEnd(interval),
			this.price
		);
	}

	isEqual (interval) {
		return (
			this.ts_start	=== interval.ts_start &&
			this.ts_end		=== interval.ts_end &&
			this.price 		=== interval.price
		)
	}

	render () {
		return '( ' +
			this.date_start.format('Y-m-d')+
			' - ' +
			this.date_end.format('Y-m-d')+
			' : ' +
			this.price +
			' )';
	}

}