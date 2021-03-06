app.controller('planAssetCtrl', function(CONFIG, $scope, $http, toaster, StringFormatService, PaginateService) {
/** ################################################################################## */
    $scope.loading = false;
    $scope.assets = [];
    $scope.pager = null;

    $scope.asset = {
        asset_id: '',
        year: (moment().year() + 543).toString(),
        in_plan: 'I',
        plan_no: '',
        faction_id: '',
        depart_id: '',
        division_id: '',
        item_id: '',
        desc: '',
        spec: '',
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
        remark: '',
        owner: '',
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

    $('#doc_date')
        .datepicker(dtpOptions)
        .datepicker('update', new Date());
        // .on('show', function (e) {
        //     $('.day').click(function(event) {
        //         event.preventDefault();
        //         event.stopPropagation();
        //     });
        // });

    $scope.setUserInfo = function(data) {
        $scope.asset.user = data.user ? data.user.toString() : '';
        $scope.asset.faction_id = data.faction ? data.faction.toString() : '';
        $scope.asset.depart_id = data.depart ? data.depart.toString() : '';

        $scope.onFactionSelected(data.faction);
        $scope.onDepartSelected(data.depart);
    };

    const clearAsset = function() {
        $scope.asset = {
            asset_id: '',
            year: (moment().year() + 543).toString(),
            in_plan: 'I',
            plan_no: '',
            faction_id: '',
            depart_id: '',
            division_id: '',
            item_id: '',
            desc: '',
            spec: '',
            price_per_unit: '',
            unit_id: '',
            amount: '',
            sum_price: '',
            request_cause: '',
            have_amount: '',
            budget_src_id: '',
            strategic_id: '1',
            service_plan_id: '',
            start_month: '',
            reason: '',
            remark: '',
            owner: '',
        };
    };

    $scope.calculateSumPrice = async function() {
        let price = $(`#price_per_unit`).val() == '' ? 0 : parseFloat($(`#price_per_unit`).val());
        let amount = $(`#amount`).val() == '' ? 0 : parseFloat($(`#amount`).val());

        $scope.asset.sum_price = price * amount;
        $('#sum_price').val(price * amount);
    };

    /** TODO: Duplicated function */
    $scope.getAll = function(event) {
        $scope.loading = true;
        $scope.assets = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${CONFIG.baseUrl}/plans/search?type=1&year=${year}&cate=${cate}&status=${status}&depart=${depart}&show_all=1`)
        .then(function(res) {
            $scope.setAssets(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setAssets = function(res) {
        const { data, ...pager } = res.data.plans;

        $scope.assets = data;
        $scope.pager = pager;
    };

    $scope.getDataWithUrl = function(e, url, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;
        $scope.assets = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${url}&type=1&year=${year}&cate=${cate}&status=${status}&depart=${depart}&show_all=1`)
        .then(function(res) {
            cb(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.onShowItemsList = function() {
        $('#item_id').val('');
        $scope.asset.item_id = '';
        $scope.asset.desc = '';
        $scope.asset.price_per_unit = '';
        $scope.asset.unit_id = '';
    };

    $scope.onSelectedItem = function(event, item) {
        if (item) {
            /** Check existed data by depart */
            let depart = $scope.asset.depart_id === '' ? 0 : $scope.asset.depart_id;

            $http.get(`${CONFIG.apiUrl}/plans/${item.id}/${$scope.asset.year}/${depart}/existed`)
            .then(function(res) {
                if (res.data.isExisted) {
                    toaster.pop('error', "????????????????????????????????????", "???????????????????????????????????????????????????????????????????????????????????????????????? !!!");
                } else {
                    $('#item_id').val(item.id);
                    $scope.asset.item_id = item.id;
                    $scope.asset.desc = item.item_name;
                    $scope.asset.price_per_unit = item.price_per_unit;
                    $scope.asset.unit_id = item.unit_id.toString();
                }
            }, function(err) {
                console.log(err);
            });
        }

        $('#items-list').modal('hide');
    };

    $scope.getById = function(id, cb) {
        $scope.loading = true;

        $http.get(`${CONFIG.apiUrl}/assets/${id}`)
        .then(function(res) {
            cb(res.data.plan);

            $scope.loading = false;
        }, function(err) {
            console.log(err);

            $scope.loading = false;
        });
    }

    $scope.setEditControls = function(plan) {
        /** Global data */
        $scope.planId                   = plan.id;
        $scope.planType                 = 1;
        /** ?????????????????????????????????????????? */
        $scope.asset.asset_id           = plan.id;
        $scope.asset.in_plan            = plan.in_plan;
        $scope.asset.year               = plan.year.toString();
        $scope.asset.plan_no            = plan.plan_no;
        $scope.asset.item_id            = plan.plan_item.item_id;
        $('#item_id').val(plan.plan_item.item_id);

        $scope.asset.desc               = plan.plan_item.item.item_name;
        $scope.asset.spec               = plan.plan_item.spec;
        $scope.asset.price_per_unit     = plan.plan_item.price_per_unit;
        $scope.asset.amount             = plan.plan_item.amount;
        $scope.asset.sum_price          = plan.plan_item.sum_price;
        $scope.asset.start_month        = plan.start_month.toString();
        $scope.asset.request_cause      = plan.request_cause;
        $scope.asset.have_amount        = plan.have_amount;
        $scope.asset.reason             = plan.reason;
        $scope.asset.remark             = plan.remark;
        $scope.asset.approved           = plan.approved;
        $scope.asset.status             = plan.status;

        /** Convert int value to string */
        $scope.asset.unit_id            = plan.plan_item.unit_id.toString();
        $scope.asset.faction_id         = plan.depart.faction_id.toString();
        $scope.asset.depart_id          = plan.depart_id.toString();
        $scope.asset.division_id        = plan.division_id ? plan.division_id.toString() : '';
        $scope.asset.budget_src_id      = plan.budget_src_id.toString();
        $scope.asset.strategic_id       = plan.strategic_id && plan.strategic_id.toString();
        $scope.asset.service_plan_id    = plan.service_plan_id && plan.service_plan_id.toString();

        /** Convert db date to thai date. */            
        // $scope.leave.leave_date         = StringFormatService.convFromDbDate(data.leave.leave_date);

        /** Generate departs and divisions data from plan */
        $scope.onFactionSelected(plan.depart.faction_id);
        $scope.onDepartSelected(plan.depart_id);
    };

    $scope.store = function(event, form) {
        event.preventDefault();

        $(`#${form}`).submit();
    }

    $scope.edit = function(id) {
        window.location.href = `${CONFIG.baseUrl}/assets/edit/${id}`;
    };

    $scope.update = function(event, form) {
        event.preventDefault();
    
        if(confirm(`????????????????????????????????????????????????????????????????????????????????? ${$scope.asset.asset_id} ???????????????????????????????`)) {
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

                    /** TODO: Reset asset model */
                    $scope.setAssets(res);
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