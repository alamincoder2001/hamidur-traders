<style>
	.v-select {
		margin-bottom: 5px;
	}

	.v-select.open .dropdown-toggle {
		border-bottom: 1px solid #ccc;
	}

	.v-select .dropdown-toggle {
		padding: 0px;
		height: 25px;
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

	#customerPayment label {
		font-size: 13px;
	}

	#customerPayment select {
		border-radius: 3px;
		padding: 0;
	}

	#customerPayment .add-button {
		padding: 2.5px;
		width: 28px;
		background-color: #298db4;
		display: block;
		text-align: center;
		color: white;
	}

	#customerPayment .add-button:hover {
		background-color: #41add6;
		color: white;
	}
</style>
<div id="customerPayment">
	<div class="row" style="border-bottom: 1px solid #ccc;padding-bottom: 15px;margin-bottom: 15px;">
		<div class="col-md-12">
			<form @submit.prevent="saveCustomerPayment">
				<div class="row">
					<div class="col-md-5 col-md-offset-1">
						<div class="form-group">
							<label class="col-md-4 control-label">Transaction Type</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<select class="form-control" v-model="payment.CPayment_TransactionType" required>
									<option value="">-- select type --</option>
									<option value="CR">Receive</option>
									<option value="CP">Payment</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Type</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<select class="form-control" v-model="payment.CPayment_Paymentby" required>
									<option value="cash">Cash</option>
									<option value="bank">Bank</option>
								</select>
							</div>
						</div>
						<div class="form-group" style="display:none;" v-bind:style="{display: payment.CPayment_Paymentby == 'bank' ? '' : 'none'}">
							<label class="col-md-4 control-label">Bank Account</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<v-select v-bind:options="filteredAccounts" v-model="selectedAccount" label="display_text" placeholder="Select account"></v-select>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Customer</label>
							<label class="col-md-1">:</label>
							<div class="col-md-6 col-xs-11">
								<select class="form-control" v-if="customers.length == 0"></select>
								<v-select v-bind:options="customers" v-model="selectedCustomer" label="display_name" @input="getCustomerDue" v-if="customers.length > 0"></v-select>
							</div>
							<div class="col-md-1 col-xs-1" style="padding-left:0;margin-left: -3px;">
								<a href="/customer" target="_blank" class="add-button"><i class="fa fa-plus"></i></a>
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Due</label>
							<label class="col-md-1">:</label>
							<div class="col-md-3">
								<input type="text" class="form-control" v-model="due" readonly>
							</div>
							<label class="col-md-1 no-padding-left">Total</label>
							<div class="col-md-3">
								<input type="text" class="form-control" v-model="payment.CPayment_previous_due" readonly>
							</div>
						</div>
					</div>

					<div class="col-md-5">
						<div class="form-group">
							<label class="col-md-4 control-label">Payment Date</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="date" class="form-control" v-model="payment.CPayment_date" required @change="getCustomerPayments" v-bind:disabled="userType == 'u' ? true : false">
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Description</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="text" class="form-control" v-model="payment.CPayment_notes">
							</div>
						</div>
						<div class="form-group" style="display: none;" :style="{display: payment.CPayment_TransactionType == 'CR' ? '' : 'none'}">
							<label class="col-md-4 control-label">Discount</label>
							<label class="col-md-1">:</label>
							<div class="col-md-3">
								<input type="number" min="0" step="0.01" id="discountPercent" class="form-control" v-model="discountPercent" v-on:input="calculateDiscount" />
							</div>
							<label class="col-xs-1 control-label no-padding-right">%</label>
							<div class="col-md-3">
								<input type="number" min="0" step="0.01" id="discount" class="form-control" v-model="payment.CPayment_discount" v-on:input="calculateDiscount" />
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-4 control-label">Amount</label>
							<label class="col-md-1">:</label>
							<div class="col-md-7">
								<input type="number" class="form-control" v-model="payment.CPayment_amount" required>
							</div>
						</div>
						<div class="form-group">
							<div class="col-md-7 col-md-offset-5">
								<input type="submit" class="btn btn-success btn-sm" value="Save">
								<input type="button" class="btn btn-danger btn-sm" value="Cancel" @click="resetForm">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

	<div class="row">
		<div class="col-sm-12 form-inline">
			<div class="form-group">
				<label for="filter" class="sr-only">Filter</label>
				<input type="text" class="form-control" v-model="filter" placeholder="Filter">
			</div>
		</div>
		<div class="col-md-12">
			<div class="table-responsive">
				<datatable :columns="columns" :data="payments" :filter-by="filter" style="margin-bottom: 5px;">
					<template scope="{ row }">
						<tr>
							<td>{{ row.CPayment_invoice }}</td>
							<td>{{ row.CPayment_date }}</td>
							<td>{{ row.Customer_Name }}</td>
							<td>{{ row.transaction_type }}</td>
							<td>{{ row.payment_by }}</td>
							<td>{{ row.CPayment_amount }}</td>
							<td>{{ row.CPayment_discount }}</td>
							<td>{{ row.CPayment_notes }}</td>
							<td>{{ row.CPayment_Addby }}</td>
							<td>
								<button type="button" class="button edit" @click="window.location = `/paymentAndReport/${row.CPayment_id}`">
									<i class="fa fa-file-o"></i>
								</button>
								<?php if ($this->session->userdata('accountType') != 'u') { ?>
									<button type="button" class="button edit" @click="editPayment(row)">
										<i class="fa fa-pencil"></i>
									</button>
									<button type="button" class="button" @click="deletePayment(row.CPayment_id)">
										<i class="fa fa-trash"></i>
									</button>
								<?php } ?>
							</td>
						</tr>
					</template>
				</datatable>
				<datatable-pager v-model="page" type="abbreviated" :per-page="per_page" style="margin-bottom: 50px;"></datatable-pager>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>assets/js/vue/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/axios.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vuejs-datatable.js"></script>
<script src="<?php echo base_url(); ?>assets/js/vue/vue-select.min.js"></script>
<script src="<?php echo base_url(); ?>assets/js/moment.min.js"></script>

<script>
	Vue.component('v-select', VueSelect.VueSelect);
	new Vue({
		el: '#customerPayment',
		data() {
			return {
				payment: {
					CPayment_id: 0,
					CPayment_customerID: null,
					CPayment_TransactionType: 'CR',
					CPayment_Paymentby: 'cash',
					account_id: null,
					CPayment_date: moment().format('YYYY-MM-DD'),
					CPayment_amount: '',
					CPayment_discount: 0,
					CPayment_notes: '',
					CPayment_previous_due: 0,
					due_previous: 0
				},
				discountPercent: 0,
				payments: [],
				customers: [],
				selectedCustomer: {
					display_name: 'Select Customer',
					Customer_Name: ''
				},
				accounts: [],
				selectedAccount: null,
				installmentDue: 0,
				due: 0,
				userType: '<?php echo $this->session->userdata("accountType"); ?>',

				columns: [{
						label: 'Transaction Id',
						field: 'CPayment_invoice',
						align: 'center'
					},
					{
						label: 'Date',
						field: 'CPayment_date',
						align: 'center'
					},
					{
						label: 'Customer',
						field: 'Customer_Name',
						align: 'center'
					},
					{
						label: 'Transaction Type',
						field: 'transaction_type',
						align: 'center'
					},
					{
						label: 'Payment by',
						field: 'payment_by',
						align: 'center'
					},
					{
						label: 'Amount',
						field: 'CPayment_amount',
						align: 'center'
					},
					{
						label: 'Discount',
						field: 'CPayment_discount',
						align: 'center'
					},
					{
						label: 'Description',
						field: 'CPayment_notes',
						align: 'center'
					},
					{
						label: 'Saved By',
						field: 'CPayment_Addby',
						align: 'center'
					},
					{
						label: 'Action',
						align: 'center',
						filterable: false
					}
				],
				page: 1,
				per_page: 10,
				filter: ''
			}
		},
		computed: {
			filteredAccounts() {
				let accounts = this.accounts.filter(account => account.status == '1');
				return accounts.map(account => {
					account.display_text = `${account.account_name} - ${account.account_number} (${account.bank_name})`;
					return account;
				})
			},
		},
		created() {
			this.getCustomers();
			this.getAccounts();
			this.getCustomerPayments();
		},
		methods: {
			getCustomerPayments() {
				let data = {
					dateFrom: this.payment.CPayment_date,
					dateTo: this.payment.CPayment_date
				}
				axios.post('/get_customer_payments', data).then(res => {
					this.payments = res.data.filter(pay => pay.CPayment_SaleInvoice == null);
				})
			},

			getCustomers() {
				axios.get('/get_customers').then(res => {
					this.customers = res.data;
				})
			},

			async getCustomerDue() {
				if (event.type == "click") {
					return;
				}
				if (this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined) {
					return;
				}

				await axios.post('/get_customer_due', {
					customerId: this.selectedCustomer.Customer_SlNo
				}).then(res => {
					this.payment.CPayment_previous_due = res.data[0].dueAmount;
				})

				await axios.post('/get_installment_due', {
					customerId: this.selectedCustomer.Customer_SlNo
				}).then(res => {
					this.installmentDue = res.data.reduce((acc, pre) => {
						return acc + pre.installments.reduce((ac, pr) => {
							return ac + +parseFloat(pr.install_due)
						}, 0)
					}, 0).toFixed(2);

					this.due = parseFloat(parseFloat(this.payment.CPayment_previous_due) - parseFloat(this.installmentDue)).toFixed(2)
				})
			},

			getAccounts() {
				axios.get('/get_bank_accounts')
					.then(res => {
						this.accounts = res.data;
					})
			},

			calculateDiscount() {
				if (event.target.id == 'discountPercent') {
					this.payment.CPayment_discount = ((parseFloat(this.due) * parseFloat(this.discountPercent)) / 100).toFixed(2);
				} else {
					this.discountPercent = (parseFloat(this.payment.CPayment_discount) / parseFloat(this.due) * 100).toFixed(2);
				}
			},

			saveCustomerPayment() {
				if (this.payment.CPayment_Paymentby == 'bank') {
					if (this.selectedAccount == null) {
						alert('Select an account');
						return;
					} else {
						this.payment.account_id = this.selectedAccount.account_id;
					}
				} else {
					this.payment.account_id = null;
				}
				if (this.selectedCustomer == null || this.selectedCustomer.Customer_SlNo == undefined) {
					alert('Select Customer');
					return;
				}

				if (parseFloat(this.due) == 0) {
					console.log(this.due);
					alert("Due amount empty");
					return
				}
				if (this.payment.CPayment_TransactionType == 'CR' && parseFloat(this.due) < parseFloat(this.payment.CPayment_amount)) {
					alert("Payment amount less than due");
					return
				}

				this.payment.due_previous = this.due;
				this.payment.CPayment_customerID = this.selectedCustomer.Customer_SlNo;

				let url = '/add_customer_payment';
				if (this.payment.CPayment_id != 0) {
					url = '/update_customer_payment';
				}
				axios.post(url, this.payment).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.resetForm();
						this.getCustomerPayments();
						let invoiceConfirm = confirm('Do you want to view invoice?');
						if (invoiceConfirm == true) {
							window.open('/paymentAndReport/' + r.paymentId, '_blank');
						}
					}
				})
			},

			editPayment(payment) {
				let keys = Object.keys(this.payment);
				keys.forEach(key => {
					this.payment[key] = payment[key];
				})
				this.due = payment.due_previous
				this.discountPercent = (parseFloat(payment.CPayment_discount) / parseFloat(this.due) * 100)

				this.selectedCustomer = {
					Customer_SlNo: payment.CPayment_customerID,
					Customer_Name: payment.Customer_Name,
					display_name: `${payment.CPayment_customerID} - ${payment.Customer_Name}`
				}

				if (payment.CPayment_Paymentby == 'bank') {
					this.selectedAccount = {
						account_id: payment.account_id,
						account_name: payment.account_name,
						account_number: payment.account_number,
						bank_name: payment.bank_name,
						display_text: `${payment.account_name} - ${payment.account_number} (${payment.bank_name})`
					}
				}
			},

			deletePayment(paymentId) {
				let deleteConfirm = confirm('Are you sure?');
				if (deleteConfirm == false) {
					return;
				}
				axios.post('/delete_customer_payment', {
					paymentId: paymentId
				}).then(res => {
					let r = res.data;
					alert(r.message);
					if (r.success) {
						this.getCustomerPayments();
					}
				})
			},

			resetForm() {
				this.payment.CPayment_id = 0;
				this.payment.CPayment_customerID = '';
				this.payment.CPayment_amount = '';
				this.payment.CPayment_discount = 0;
				this.payment.CPayment_notes = '';
				this.discountPercent = 0

				this.selectedCustomer = {
					display_name: 'Select Customer',
					Customer_Name: ''
				}

				this.payment.CPayment_previous_due = 0;
				this.due = 0;
			}
		}
	})
</script>