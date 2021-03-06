app.controller('planServiceCtrl', function(CONFIG, $scope, $http, toaster, StringFormatService, PaginateService) {
/** ################################################################################## */
    $scope.loading = false;
    $scope.services = [];
    $scope.pager = [];

    $scope.service = {
        service_id: '',
        in_plan: 'I',
        year: (moment().year() + 543).toString(),
        plan_no: '',
        faction_id: '',
        depart_id: '',
        division_id: '',
        item_id: '',
        desc: '',
        price_per_unit: '',
        unit_id: '',
        amount: '',
        sum_price: '',
        request_cause: '',
        have_amount: '',
        budget_src_id: '1',
        strategic_id: '',
        service_plan_id: '',
        start_month: '',
        reason: '',
        remark: ''
    };

    /** ============================== Init Form elements ============================== */
    let dtpDateOptions = {
        autoclose: true,
        language: 'th',
        format: 'dd/mm/yyyy',
        thaiyear: true,
        todayBtn: true,
        todayHighlight: true
    };

    $('#doc_date')
        .datepicker(dtpDateOptions)
        .datepicker('update', new Date());
        // .on('show', function (e) {
        //     $('.day').click(function(event) {
        //         event.preventDefault();
        //         event.stopPropagation();
        //     });
        // });

    $scope.setUserInfo = function(data) {
        $scope.service.user = data.user ? data.user.toString() : '';
        $scope.service.faction_id = data.faction ? data.faction.toString() : '';
        $scope.service.depart_id = data.depart ? data.depart.toString() : '';

        $scope.onFactionSelected(data.faction);
        $scope.onDepartSelected(data.depart);
    };

    $scope.clearService = function() {
        $scope.service = {
            service_id: '',
            in_plan: 'I',
            year: (moment().year() + 543).toString(),
            plan_no: '',
            faction_id: '',
            depart_id: '',
            division_id: '',
            item_id: '',
            desc: '',
            price_per_unit: '',
            unit_id: '',
            amount: '',
            sum_price: '',
            request_cause: '',
            have_amount: '',
            budget_src_id: '1',
            strategic_id: '',
            service_plan_id: '',
            start_month: '',
            reason: '',
            remark: ''
        };
    };

    $scope.calculateSumPrice = async function() {
        let price = $(`#price_per_unit`).val() == '' ? 0 : parseFloat($(`#price_per_unit`).val());
        let amount = $(`#amount`).val() == '' ? 0 : parseFloat($(`#amount`).val());

        $scope.service.sum_price = price * amount;
        $('#sum_price').val(price * amount);
    };

    $scope.getAll = function(event) {
        $scope.loading = true;
        $scope.services = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${CONFIG.baseUrl}/plans/search?type=3&year=${year}&cate=${cate}&status=${status}&depart=${depart}`)
        .then(function(res) {
            $scope.setServices(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setServices = function(res) {
        const { data, ...pager } = res.data.plans;

        $scope.services = data;
        $scope.pager = pager;
    };

    $scope.getDataWithUrl = function(e, url, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;
        $scope.services = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${url}&type=3&year=${year}&cate=${cate}&status=${status}&depart=${depart}`)
        .then(function(res) {
            cb(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.onSelectedItem = function(event, item) {
        if (item) {
            /** Check existed data by depart */
            let depart = $scope.service.depart_id === '' ? 0 : $scope.service.depart_id;

            $http.get(`${CONFIG.apiUrl}/plans/${item.id}/${$scope.service.year}/${depart}/existed`)
            .then(function(res) {
                if (res.data.isExisted) {
                    toaster.pop('error', "????????????????????????????????????", "???????????????????????????????????????????????????????????????????????????????????????????????? !!!");
                } else {
                    $('#item_id').val(item.id);
                    $scope.service.item_id = item.id;
                    $scope.service.desc = item.item_name;
                    $scope.service.price_per_unit = item.price_per_unit;
                    $scope.service.unit_id = item.unit_id.toString();
                }
            }, function(err) {
                console.log(err);
            });
        }

        $('#items-list').modal('hide');
    };

    $scope.getById = function(id, cb) {
        $http.get(`${CONFIG.apiUrl}/services/${id}`)
        .then(function(res) {
            cb(res.data.plan);
        }, function(err) {
            console.log(err);
        });
    }

    $scope.setEditControls = function(plan) {
        /** Global data */
        $scope.planId                   = plan.id;
        $scope.planType                 = 3;

        /** ???????????????????????????????????????????????? */
        $scope.service.service_id       = plan.id;
        $scope.service.in_plan          = plan.in_plan;
        $scope.service.year             = plan.year.toString();
        // $scope.service.plan_no          = plan.plan_no;
        $scope.service.desc             = plan.plan_item.item.item_name;
        $scope.service.item_id          = plan.plan_item.item_id;
        $('#item_id').val(plan.plan_item.item_id);

        $scope.service.price_per_unit   = plan.plan_item.price_per_unit;
        $scope.service.amount           = plan.plan_item.amount;
        $scope.service.sum_price        = plan.plan_item.sum_price;
        $scope.service.start_month      = plan.start_month.toString();
        $scope.service.request_cause    = plan.request_cause;
        $scope.service.have_amount      = plan.have_amount;
        $scope.service.reason           = plan.reason;
        $scope.service.remark           = plan.remark;
        $scope.service.approved         = plan.approved;
        $scope.service.status           = plan.status;

        /** Convert int value to string */
        $scope.service.unit_id          = plan.plan_item.unit_id.toString();
        $scope.service.faction_id       = plan.depart.faction_id.toString();
        $scope.service.depart_id        = plan.depart_id.toString();
        $scope.service.division_id      = plan.division_id ? plan.division_id.toString() : '';
        $scope.service.budget_src_id    = plan.budget_src_id.toString();
        $scope.service.strategic_id     = plan.strategic_id && plan.strategic_id.toString();
        $scope.service.service_plan_id  = plan.service_plan_id && plan.service_plan_id.toString();

        /** Generate departs and divisions data from plan */
        $scope.onFactionSelected(plan.depart.faction_id);
        $scope.onDepartSelected(plan.depart_id);
    };

    $scope.store = function(event, form) {
        event.preventDefault();

        $(`#${form}`).submit();
    }

    $scope.edit = function(id) {
        window.location.href = `${CONFIG.baseUrl}/services/edit/${id}`;
    };

    $scope.update = function(event, form) {
        event.preventDefault();
    
        if(confirm(`??????????????????????????????????????????????????????????????????????????????????????? ${$scope.service.service_id} ???????????????????????????????`)) {
            $(`#${form}`).submit();
        }
    };

    $scope.delete = function(e, id) {
        e.preventDefault();
        $scope.loading = true;

        if(confirm(`???????????????????????????????????????????????????????????????????????? ${id} ???????????????????????????????`)) {
            $http.delete(`${CONFIG.baseUrl}/plans/${id}`)
            .then(res => {
                console.log(res);
                if (res.data.status == 1) {
                    toaster.pop('success', "??????????????????????????????", "??????????????????????????????????????????????????? !!!");

                    /** TODO: Reset service model */
                    $scope.setServices(res);
                } else {
                    toaster.pop('error', "????????????????????????????????????", "???????????????????????????????????????????????????????????? !!!");
                }

                $scope.loading = false;
            }, err => {
                console.log(err);

                $scope.loading = false;
                toaster.pop('error', "????????????????????????????????????", "???????????????????????????????????????????????????????????? !!!");
            });
        }
    };
});