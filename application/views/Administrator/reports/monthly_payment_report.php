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

	tr.red td {
		color: red !important;
	}

	tr.green td {
		color: green !important
	}

	.btnSMS {
		background: #00a9d1 !important;
		border: 1px solid #ccc;
		padding: 3px 14px;
		color: white;
	}

	.btnSMS:hover {
		background: #03cfff !important;
		border: 1px solid #ccc;
		padding: 3px 14px;
		color: white;
	}
</style>
<div class="row" id="customerPaymentReport">
	<div class="col-xs-12 col-md-12 col-lg-12" style="border-bottom:1px #ccc solid;">
		<!-- <div class="form-group">
			<label class="col-sm-1 control-label no-padding-right"> Customer </label>
			<div class="col-sm-2">
				<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name"></v-select>
			</div>
		</div> -->

		<div class="form-group">
			<label class="col-sm-1 control-label no-padding-right"> Date from </label>
			<div class="col-sm-2">
				<input type="date" class="form-control" v-model="dateFrom">
			</div>
			<label class="col-sm-1 control-label no-padding-right text-center" style="width:30px"> to </label>
			<div class="col-sm-2">
				<input type="date" class="form-control" v-model="dateTo">
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-1">
				<input type="button" class="btn btn-primary" value="Show" v-on:click="getReport" style="margin-top:0px;border:0px;height:28px;">
			</div>
		</div>
	</div>

	<div class="col-sm-12">
		<!-- <a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
			<i class="fa fa-print"></i> Print
		</a> -->
		<div class="row">
			<div class="col-md-6">
				<a href="" style="margin: 7px 0;display:block;width:50px;" v-on:click.prevent="print">
					<i class="fa fa-print"></i> Print
				</a>
			</div>
			<div class="col-md-6 text-right" style="padding-top: 4px;">
				<button v-on:click.prevent="sendSMS" class="btnSMS">Send SMS to Due Customer</button>
			</div>
		</div>
		<div class="table-responsive" id="reportTable">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th style="text-align:center">SL</th>
						<th style="text-align:center">Customer Code</th>
						<th style="text-align:center">Customer Name</th>
						<th style="text-align:center">Customer Mobile</th>
						<th style="text-align:center">Customer Address</th>
						<th style="text-align:center">Due Date</th>
						<th style="text-align:center">Installment Amount</th>
						<th style="text-align:center">Payment Amount</th>
						<th style="text-align:center">Action</th>
					</tr>
				</thead>
				<tbody>
					<template v-for="(row,sl) in payments">
						<tr :class="row.paid > 0 ? 'green' : 'red'">
							<!-- <tr style="color:white;" :style="{background: row.payment > 0 ? 'green' : 'red'}"> -->
							<td style="text-align:center;">{{ sl + 1 }}</td>
							<td style="text-align:center;">{{ row.Customer_Code }}</td>
							<td style="text-align:left;">{{ row.Customer_Name }}</td>
							<td style="text-align:center;">{{ row.Customer_Mobile }}</td>
							<td style="text-align:center;">{{ row.Customer_Address }}</td>
							<td style="text-align:center;">
								<div class="input-group" v-if="eyeShow == true && rowSl == sl">
									<input type="date" v-model="row.installment_date" style="width: 115px;padding: 0 5px;" />
									<button @click="updateDate(row)" style="padding: 0;color: #fdfdfd;background: #000b8f;">update</button>
								</div>
								<span v-else>{{ row.installment_date }}</span>
							</td>
							<td style="text-align:right;">{{ parseFloat(row.installment_amount).toFixed(2) }}</td>
							<td style="text-align:right;">{{ parseFloat(row.paid).toFixed(2) }}</td>
							<td>
								<i v-if="eyeShow == true && rowSl == sl" @click="eyeToggle(sl)" class="fa fa-eye-slash" style="cursor: pointer;"></i>
								<i v-else @click="eyeToggle(sl)" class="fa fa-eye" style="cursor: pointer;"></i>
							</td>
						</tr>
					</template>
				</tbody>
				<tbody v-if="payments.length == 0">
					<tr>
						<td colspan="8">No records found</td>
					</tr>
				</tbody>

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
				dateFrom: null,
				dateTo: null,
				payments: [],
				previousBalance: 0.00,
				showTable: false,
				eyeShow: false,
				rowSl: '',
			}
		},
		created() {
			let today = moment().format('YYYY-MM-DD');
			this.dateTo = today;
			this.dateFrom = moment().subtract(1, 'months').format('YYYY-MM-DD');
			this.getReport();
		},
		methods: {
			eyeToggle(sl) {
				this.rowSl = '';
				if (this.eyeShow) {
					this.eyeShow = false
				} else {
					this.eyeShow = true;
					this.rowSl = sl;
				}
			},

			updateDate(rowData) {
				let filter = {
					installment_id: rowData.installment_id,
					installment_date: rowData.installment_date
				}
				axios.post('/customer_installment_dateupdate', filter).then(res => {
					if (res.data.success) {
						alert(res.data.message);
						this.eyeShow = false;
						this.rowSl = '';
						this.getReport();
					}else{
						console.log(res.data.message);
					}
				})
			},
			sendSMS() {
				let customers = this.payments.filter(c => c.Customer_Mobile.length == 11 && c.paid == 0)
				axios.post('/send_sms_due_customers', {
					customers
				}).then(res => {
					console.log(res);
				})
			},
			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},
			getReport() {
				// if (this.selectedCustomer == null) {
				// 	alert('Select customer');
				// 	return;
				// }
				let data = {
					dateFrom: this.dateFrom,
					dateTo: this.dateTo,
					// customerId: this.selectedCustomer.Customer_SlNo
				}

				axios.post('/get_customer_payments_monthly', data).then(res => {
					this.payments = res.data.filter(pay => parseFloat(pay.installment_amount) > parseFloat(pay.paid));
				})
			},
			async print() {
				let reportContent = `
					<div class="container">
						<h4 style="text-align:center">Monthly Customer Payment Report</h4 style="text-align:center">
						<div class="row">
							<div class="col-xs-6" style="font-size:12px;">
							</div>
							<div class="col-xs-6 text-right">
								<strong>Statement from</strong> ${this.dateFrom} <strong>to</strong> ${this.dateTo}
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