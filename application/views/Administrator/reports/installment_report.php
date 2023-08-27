<style>
	.v-select {
		margin-bottom: 5px;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
	}

	.v-select input[type=search],
	.v-select input[type=search]:focus {
		margin: 0px;
	}

	.v-select .vs__selected-options {
		overflow: hidden;
		flex-wrap: nowrap;
	}

	.v-select .selected-tag {
		margin: 2px 0px;
		white-space: nowrap;
		position: absolute;
		left: 0px;
	}

	.v-select .vs__actions {
		margin-top: -5px;
	}

	.v-select .dropdown-menu {
		width: auto;
		overflow-y: auto;
	}
</style>
<div class="row" id="customerPaymentReport">
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
		<div class="form-group">
			<label class="col-sm-1 control-label no-padding-right"> Customer </label>
			<div class="col-sm-2">
				<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" @input="getCustomerInvoices"></v-select>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-1 control-label no-padding-right"> Inst. Invoice </label>
			<div class="col-sm-2">
				<v-select v-bind:options="invoices" v-model="selectedInvoice" label="SaleMaster_InvoiceNo"></v-select>
			</div>
		</div>

		<!-- <div class="form-group">
			<label class="col-sm-1 control-label no-padding-right"> Date from </label>
			<div class="col-sm-2">
				<input type="date" class="form-control" v-model="dateFrom">
			</div>
			<label class="col-sm-1 control-label no-padding-right text-center" style="width:30px"> to </label>
			<div class="col-sm-2">
				<input type="date" class="form-control" v-model="dateTo">
			</div>
		</div> -->

		<div class="form-group">
			<div class="col-sm-1">
				<input type="button" class="btn btn-primary" value="Show" v-on:click="getReport" style="margin-top:0px;border:0px;height:28px;">
			</div>
		</div>
	</div>

	<div class="col-sm-12" style="display:none;" v-bind:style="{display: showTable ? '' : 'none'}">
		<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
			<i class="fa fa-print"></i> Print
		</a>
		<div class="table-responsive" id="reportTable">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th style="text-align:center">Date</th>
						<th style="text-align:center">Customer Name</th>
						<th style="text-align:center">Customer Mobile</th>
						<th style="text-align:center">Sale Invoice</th>
						<th style="text-align:center">Install Date</th>
						<th style="text-align:center">Install Number</th>
						<th style="text-align:center">Install Amount</th>
						<th style="text-align:center">Install Paid</th>
						<th style="text-align:center">Install Due</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="install in installment">
						<td style="text-align:center;">{{ install.date }}</td>
						<td style="text-align:center;">{{ selectedCustomer.Customer_Name }}</td>
						<td style="text-align:center;">{{ selectedCustomer.Customer_Mobile }}</td>
						<td style="text-align:center;">{{ selectedInvoice.SaleMaster_InvoiceNo }}</td>
						<td style="text-align:center;">{{ install.installment_date }}</td>
						<td style="text-align:center;">{{ install.installment_number }}</td>
						<td style="text-align:center;">{{ install.installment_amount }}</td>
						<td style="text-align:center;">{{ install.install_paid }}</td>
						<td style="text-align:center;">{{ install.install_due }}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr style="font-weight: 600;">
						<td colspan="5" style="text-align: right;">Total</td>
						<td style="text-align: center;">{{ (installment.reduce((prev,curr) => {return +prev + +curr.installment_amount},0)).toFixed(2) }}</td>
						<td style="text-align: center;">{{ (installment.reduce((prev,curr) => {return +prev + +curr.install_paid},0)).toFixed(2) }}</td>
						<td style="text-align: center;">{{ (installment.reduce((prev,curr) => {return +prev + +curr.install_due},0)).toFixed(2) }}</td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#customerPaymentReport',
		data() {
			return {
				customers: [],
				selectedCustomer: null,
				// dateFrom: null,
				// dateTo: null,
				today: moment().format('YYYY-MM-DD'),
				installment: [],
				// previousBalance: 0.00,
				showTable: false,

				invoices: [],
				selectedInvoice: {
					SaleMaster_SlNo: '',
					SaleMaster_InvoiceNo: 'Select'
				},
			}

		},
		created() {
			// let today = moment().format('YYYY-MM-DD');
			// this.dateTo = today;
			// this.dateFrom = moment().format('YYYY-MM-DD');
			this.getCustomers();
		},
		methods: {
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			async getCustomerInvoices() {
				if (this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined) {
					return;
				}

				await axios.post('/get_installment_due', {
					customerId: this.selectedCustomer.Customer_SlNo
				}).then(res => {
					this.invoices = res.data;
				})
			},
			getReport() {
				if (this.selectedCustomer == null) {
					alert('Select customer');
					return;
				}
				if (this.selectedInvoice.SaleMaster_SlNo == '') {
					alert('Select a Invoice');
					return;
				}

				this.installment = this.selectedInvoice.installments
				this.showTable = true;

				// let data = {
				// 	dateFrom: this.dateFrom,
				// 	dateTo: this.dateTo,
				// 	customerId: this.selectedCustomer.Customer_SlNo
				// }

				// axios.post('/get_customer_ledger', data).then(res => {
				// 	this.payments = res.data.payments;
				// 	this.previousBalance = res.data.previousBalance;
				// 	this.showTable = true;
				// })
			},
			async print() {
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Customer Install Report</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-6" style="font-size:12px;">
								<strong>Customer Code: </strong> ${this.selectedCustomer.Customer_Code}<br>
								<strong>Name: </strong> ${this.selectedCustomer.Customer_Name}<br>
								<strong>Address: </strong> ${this.selectedCustomer.Customer_Address}<br>
								<strong>Mobile: </strong> ${this.selectedCustomer.Customer_Mobile}<br>
							</div>
							<div class="col-xs-6 text-right">
								<strong>Statement Date</strong> ${this.today} <strong>
							</div>
						</div>
					</div>
					<div class="container">
						<div class="row">
							<div class="col-xs-12">
								${document.querySelector('#reportTable').innerHTML}
							</div>
						</div>
					</div>
				`;

				var mywindow = window.open('', 'PRINT', `width=${screen.width}, height=${screen.height}`);
				mywindow.document.write(`
					<?php $this->load->view('Administrator/reports/reportHeader.php'); ?>
				`);

				mywindow.document.body.innerHTML += reportContent;

				mywindow.focus();
				await new Promise(resolve => setTimeout(resolve, 1000));
				mywindow.print();
				mywindow.close();
			}
		}
	})
</script>