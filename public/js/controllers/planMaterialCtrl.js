app.controller('planMaterialCtrl', function(CONFIG, $scope, $http, toaster, StringFormatService, PaginateService) {
/** ################################################################################## */
    $scope.loading = false;
    $scope.materials = [];
    $scope.pager = [];

    $scope.material = {
        material_id: '',
        in_plan: 'I',
        year: (moment().year() + 543).toString(),
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
        $scope.material.user = data.user ? data.user.toString() : '';
        $scope.material.faction_id = data.faction ? data.faction.toString() : '';
        $scope.material.depart_id = data.depart ? data.depart.toString() : '';

        $scope.onFactionSelected(data.faction);
        $scope.onDepartSelected(data.depart);
    };

    $scope.clearMaterial = function() {
        $scope.material = {
            material_id: '',
            in_plan: 'I',
            year: (moment().year() + 543).toString(),
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
            start_month: '',
            request_cause: '',
            have_amount: '',
            budget_src_id: '1',
            strategic_id: '',
            service_plan_id: '',
            reason: '',
            remark: '',
        };
    };

    $scope.calculateSumPrice = async function() {
        let price = $(`#price_per_unit`).val() == '' ? 0 : parseFloat($(`#price_per_unit`).val());
        let amount = $(`#amount`).val() == '' ? 0 : parseFloat($(`#amount`).val());

        $scope.material.sum_price = price * amount;
        $('#sum_price').val(price * amount);
    };

    $scope.getAll = function(inStock) {
        $scope.loading = true;
        $scope.materials = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${CONFIG.baseUrl}/plans/search?type=2&year=${year}&cate=${cate}&status=${status}&depart=${depart}&in_stock=${inStock}`)
        .then(function(res) {
            $scope.setMaterials(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setMaterials = function(res) {
        const { data, ...pager } = res.data.plans;

        $scope.materials = data;
        $scope.pager = pager;
    };

    $scope.getDataWithUrl = function(e, url, inStock, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;
        $scope.materials = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${url}&type=2&year=${year}&cate=${cate}&status=${status}&depart=${depart}&in_stock=${inStock}`)
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
            $('#item_id').val(item.id);
            $scope.material.item_id = item.id;
            $scope.material.desc = item.item_name;
            $scope.material.price_per_unit = item.price_per_unit;
            $scope.material.unit_id = item.unit_id.toString();
        }

        $('#items-list').modal('hide');
    };

    $scope.getById = function(id, cb) {
        $http.get(`${CONFIG.apiUrl}/materials/${id}`)
        .then(function(res) {
            cb(res.data.plan);
        }, function(err) {
            console.log(err);
        });
    };

    $scope.setEditControls = function(plan) {
        /** Global data */
        $scope.planId                   = plan.id;
        $scope.planType                 = 2;

        /** ????????????????????????????????? */
        $scope.material.material_id     = plan.id;
        $scope.material.in_plan         = plan.in_plan;
        $scope.material.year            = plan.year.toString();
        // $scope.material.plan_no         = plan.plan_no;
        $scope.material.desc            = plan.plan_item.item.item_name;
        $scope.material.item_id         = plan.plan_item.item_id;
        $('#item_id').val(plan.plan_item.item_id);

        $scope.material.spec            = plan.plan_item.spec;
        $scope.material.price_per_unit  = plan.plan_item.price_per_unit;
        $scope.material.amount          = plan.plan_item.amount;
        $scope.material.sum_price       = plan.plan_item.sum_price;
        $scope.material.start_month     = plan.start_month.toString();
        $scope.material.request_cause   = plan.request_cause;
        $scope.material.have_amount     = plan.have_amount;
        $scope.material.reason          = plan.reason;
        $scope.material.remark          = plan.remark;
        $scope.material.approved        = plan.approved;
        $scope.material.status          = plan.status;

        /** Convert int value to string */
        $scope.material.unit_id         = plan.plan_item.unit_id.toString();
        $scope.material.faction_id      = plan.depart.faction_id.toString();
        $scope.material.depart_id       = plan.depart_id.toString();
        $scope.material.division_id     = plan.division_id ? plan.division_id.toString() : '';
        $scope.material.budget_src_id   = plan.budget_src_id.toString();
        $scope.material.strategic_id    = plan.strategic_id && plan.strategic_id.toString();
        $scope.material.service_plan_id = plan.service_plan_id && plan.service_plan_id.toString();

        /** Generate departs and divisions data from plan */
        $scope.onFactionSelected(plan.depart.faction_id);
        $scope.onDepartSelected(plan.depart_id);
    };

    $scope.store = function(event, form) {
        event.preventDefault();

        $(`#${form}`).submit();
    };

    $scope.edit = function(id) {
        window.location.href = `${CONFIG.baseUrl}/materials/edit/${id}`;
    };

    $scope.update = function(event, form) {
        event.preventDefault();
    
        if(confirm(`????????????????????????????????????????????????????????????????????????????????????????????? ${$scope.material.material_id} ???????????????????????????????`)) {
            $(`#${form}`).submit();
        }
    };

    $scope.delete = function(e, id) {
        e.preventDefault();
        $scope.loading = true;

        if(confirm(`????????????????????????????????????????????????????????? ${id} ???????????????????????????????`)) {
            $http.delete(`${CONFIG.baseUrl}/plans/${id}`)
            .then(res => {
                console.log(res);
                if (res.data.status == 1) {
                    toaster.pop('success', "??????????????????????????????", "??????????????????????????????????????????????????? !!!");

                    /** TODO: Reset material model */
                    $scope.setMaterials(res);
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

    $scope.addFromLastYear = function() {
        if(confirm(`?????????????????????????????????????????????????????????????????????????????????????????????????????????????????????????`)) {
            $('#progress-form').modal('show');
        }
    }
});