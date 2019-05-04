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
					if (confirm('Are you sure you want to delete this interval?') ) {
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
		var next_item = null; let d_min = 0;
		for (let i in this.items) {
			let new_d_min = this.items[i].sort - item.sort;
			if (new_d_min >= 0) {
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

		// Start & end timestamps
		//this.ts_start	= (new Date(data.date_start).getTime() / 1000);
		//this.ts_end		= (new Date(data.date_end).getTime() / 1000);

		// Sort value
		this.sort = data.date_start; //(this.ts_start +'.'+ this.data.id)*1;

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

	refresh () {

		let items = [];
		for (var i in this.widget.items) {
			items.push (this.widget.items[i]);
		}
		console.log(items);



		this.box.html('result: '+this.widget.items);
	}

}