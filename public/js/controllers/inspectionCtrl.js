app.controller('inspectionCtrl', function(CONFIG, $scope, $http, toaster, StringFormatService, PaginateService) {
    /** ################################################################################## */
    $scope.loading = false;
    $scope.inspections = [];
    $scope.pager = [];

    $scope.orders = [];
    $scope.orders_pager = null;

    $scope.inspection = {
        order_id: '',
        order: null,
        deliver_seq: '',
        deliver_no: '',
        deliver_doc_id: '',
        inspect_sdate: '',
        inspect_edate: '',
        inspect_total: '',
        inspect_result: '',
        remark: '',
    };

    $scope.newItem = {
        plan_no: '',
        plan_detail: '',
        plan_depart: '',
        plan_id: '',
        item_id: '',
        price_per_unit: '',
        unit_id: '',
        amount: '',
        sum_price: ''
    };

    /** ============================== Init Form elements ============================== */
    let dtpOptions = {
        autoclose: true,
        language: 'th',
        format: 'dd/mm/yyyy',
        thaiyear: true,
        todayBtn: true,
        todayHighlight: true
    };

    /** ==================== Add form ==================== */
    $('#inspect_sdate')
        .datepicker(dtpOptions)
        .datepicker('update', new Date())
        .on('changeDate', function(event) {
            console.log(event.date);
        });

    $('#inspect_edate')
        .datepicker(dtpOptions)
        .datepicker('update', new Date())
        .on('changeDate', function(event) {
            console.log(event.date);
        });

    $scope.showPopup = false;
    $scope.deliverBillsList = [];

    $scope.fetchDeliverBills = function(e) {
        let keyword = e.target.value;
        
        if (keyword != '') {
            $http.get(`${CONFIG.baseUrl}/inspections/${keyword}/deliver-bills`)
            .then((res) => {
                if (res.data.deliver_bills.length > 0) {
                    $scope.deliverBillsList = [ ...new Set(res.data.deliver_bills) ];
                }

                $scope.showPopup = true;
            }, (err) => {
                console.log(err);
            });
        }
    };

    $scope.setDeliverBill = function(bill) {
        $scope.showPopup = false;

        console.log(bill);
        $('#deliver_bill').val(bill);
    };

    $scope.calculateSumPrice = function() {
        let price = parseFloat($(`#price_per_unit`).val());
        let amount = parseFloat($(`#amount`).val());

        $scope.newItem.sum_price = price * amount;
        $('#sum_price').val(price * amount);
    };

    $scope.calculateVat = function() {
        let total = parseFloat($(`#total`).val());
        let rate = parseFloat($(`#vat_rate`).val());
        let vat = (total * rate) / 100;

        $scope.order.vat = vat;
        $('#vat').val(vat);

        $scope.calculateNetTotal();
    };

    $scope.calculateNetTotal = function() {
        let total = parseFloat($(`#total`).val());
        let vat = parseFloat($(`#vat`).val());

        let net_total = total + vat;

        $scope.order.net_total = net_total;
        $('#net_total').val(net_total);
    };

    $scope.addOrderItem = () => {
        $scope.order.details.push({ ...$scope.newItem });

        $scope.calculateTotal();
        $scope.clearNewItem();
    };

    $scope.removeOrderItem = (index) => {
        console.log(index);
        // $scope.order.details.push({ ...$scope.newItem });

        $scope.calculateTotal();
    };

    $scope.clearNewItem = () => {
        $scope.newItem = {
            plan_no: '',
            plan_detail: '',
            plan_depart: '',
            plan_id: '',
            item_id: '',
            price_per_unit: '',
            unit_id: '',
            amount: '',
            sum_price: ''
        };
    }

    $scope.calculateTotal = () => {
        let total = 0;

        total = $scope.order.details.reduce((sum, curVal) => {
            return sum = sum + curVal.sum_price;
        }, 0);

        $scope.order.total = total;
        $('#total').val(total);
    };

    $scope.showOrdersList = (e) => {
        $scope.loading = true;
        $scope.orders = [];
        $scope.orders_pager = null;

        $http.get(`${CONFIG.baseUrl}/orders/search?status=0`)
        .then(function(res) {
            $scope.setOrder(res);

            $scope.loading = false;

            $('#orders-list').modal('show');
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.getOrder = () => {
        $scope.loading = true;
        $scope.orders = [];
        $scope.orders_pager = null;

        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let type    = $scope.cboPlanType === '' ? '' : $scope.cboPlanType;

        $http.get(`${CONFIG.baseUrl}/orders/search?type=${type}&cate=${cate}&status=0`)
        .then(function(res) {
            $scope.setorder(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setOrder = function(res) {
        const { data, ...pager } = res.data.orders;

        console.log(data);
        $scope.orders = data;
        $scope.orders_pager = pager;
    };

    $scope.onSelectedOrder = (e, order) => {
        console.log(order);
        if (order) {
            $scope.inspection = {
                order: order,
                order_id: order.id,
                inspect_total: order.net_total
            };
        }

        $('#orders-list').modal('hide');
    };

    $scope.getAll = function() {
        $scope.orders = [];
        $scope.pager = null;
        
        $scope.loading = true;
        
        // let year    = $scope.cboYear === '' ? 0 : $scope.cboYear;
        // let type    = $scope.cboLeaveType === '' ? 0 : $scope.cboLeaveType;
        // let status  = $scope.cboLeaveStatus === '' ? '-' : $scope.cboLeaveStatus;
        // let menu    = $scope.cboMenu === '' ? 0 : $scope.cboMenu;
        // let query   = $scope.cboQuery === '' ? '' : `?${$scope.cboQuery}`;
        
        $http.get(`${CONFIG.baseUrl}/inspections/search`)
        .then(function(res) {
            $scope.setInspections(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setInspections = function (res) {
        const { data, ...pager } = res.data.inspections;

        $scope.inspections = data;
        $scope.pager = pager;
    };

    $scope.getDataWithURL = function(e, URL, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;

        $http.get(URL)
        .then(function(res) {
            cb(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.showOrderDetails = (items) => {
        if (items) {
            $scope.assets = items;
    
            $('#order-details').modal('show');
        }
    };

    $scope.store = function(event, form) {
        event.preventDefault();

        let data = {
            deliver_seq: $('#deliver_seq').val(),
            deliver_no: $('#deliver_no').val(),
            po_id: $('#po_id').val(),
            inspect_sdate: $('#inspect_sdate').val(),
            inspect_edate: $('#inspect_edate').val(),
            inspect_total: $('#inspect_total').val().replace(',', ''),
            inspect_result: $('#inspect_result').val(),
            inspect_user: $('#inspect_user').val(),
            remark: $('#remark').val(),
        };

        $http.post(`${CONFIG.baseUrl}/inspections/store`, data)
        .then(function(res) {
            console.log(res.data);
        }, function(err) {
            console.log(err);
        });
    }

    $scope.edit = function(id) {
        $http.get(`${CONFIG.baseUrl}/orders/getOrder/${id}`)
        .then(res => {
            $scope.order.id = res.data.order.id;
            $scope.order.year = res.data.order.year.toString();
            $scope.order.supplier_id = res.data.order.supplier.supplier_name;
            $scope.order.po_no = res.data.order.po_no;
            $scope.order.po_date = StringFormatService.convFromDbDate(res.data.order.po_date);
            $scope.order.remark = res.data.order.remark;
            $scope.order.total = res.data.order.total;
            $scope.order.vat_rate = res.data.order.vat_rate+'%';
            $scope.order.vat = res.data.order.vat;
            $scope.order.net_total = res.data.order.net_total;
            $scope.order.details = res.data.order.details;

            $('#po_date')
                .datepicker(dtpOptions)
                .datepicker('update', moment(res.data.order.po_date).toDate());
        }, err => {
            console.log(err);
        });
    };

    $scope.update = function(event, form) {
        event.preventDefault();

        if(confirm(`คุณต้องแก้ไขรายการขอยกเลิกวันลาเลขที่ ${$scope.cancellation.leave_id} ใช่หรือไม่?`)) {
            $(`#${form}`).submit();
        }
    };

    $scope.delete = function(e, id) {
        e.preventDefault();

        const actionUrl = $('#frmDelete').attr('action');
        $('#frmDelete').attr('action', `${actionUrl}/${id}`);

        if (window.confirm(`คุณต้องลบรายการขอยกเลิกวันลาเลขที่ ${id} ใช่หรือไม่?`)) {
            $('#frmDelete').submit();
        }
    };
});
