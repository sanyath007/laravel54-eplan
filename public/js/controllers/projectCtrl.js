app.controller('projectCtrl', function(CONFIG, $scope, $http, toaster, StringFormatService, PaginateService) {
/** ################################################################################## */
    $scope.loading = false;
    $scope.projects = [];
    $scope.pager = null;

    $scope.project = {
        project_id: '',
        year: '',
        strategic_id: '',
        strategy_id: '',
        kpi_id: '',
        project_no: '',
        project_name: '',
        total_budget: '',
        budget_src_id: '',
        faction_id: '',
        depart_id: '',
        owner_person: '',
        start_month: '',
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

    const clearProjectObj = function() {
        $scope.project = {
            project_id: '',
            year: '',
            strategic_id: '',
            strategy_id: '',
            kpi_id: '',
            project_no: '',
            project_name: '',
            total_budget: '',
            budget_src_id: '',
            faction_id: '',
            depart_id: '',
            owner_person: '',
            start_month: '',
            remark: '',
        };
    };

    $scope.getAll = function(event) {
        $scope.loading = true;
        $scope.projects = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${CONFIG.baseUrl}/api/projects?year=${year}&cate=${cate}&status=${status}&depart=${depart}`)
        .then(function(res) {
            console.log(res);
            $scope.setProjects(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setProjects = function(res) {
        const { data, ...pager } = res.data.projects;

        $scope.projects = data;
        $scope.pager = pager;
    };

    $scope.onSelectedItem = function(event, item) {
        if (item) {
            $('#item_id').val(item.id);
            $scope.asset.item_id = item.id;
            $scope.asset.desc = item.item_name;
            $scope.asset.price_per_unit = item.price_per_unit;
            $scope.asset.unit_id = item.unit_id.toString();
            $scope.asset.category_id = item.category_id.toString();
        }

        $('#items-list').modal('hide');
    };

    $scope.getDataWithUrl = function(e, url, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;
        $scope.projects = [];
        $scope.pager = null;

        let year    = $scope.cboYear === '' ? '' : $scope.cboYear;
        let cate    = $scope.cboCategory === '' ? '' : $scope.cboCategory;
        let depart  = $scope.cboDepart === '' ? '' : $scope.cboDepart;
        let status  = $scope.cboStatus === '' ? '' : $scope.cboStatus;
        let menu    = $scope.cboMenu === '' ? '' : $scope.cboMenu;

        $http.get(`${url}&year=${year}&cate=${cate}&status=${status}&depart=${depart}&menu=${menu}`)
        .then(function(res) {
            cb(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.showPersonList = (_selectedMode) => {
        $('#persons-list').modal('show');

        $scope.getPersons();

        $scope.selectedMode = _selectedMode;
    };

    $scope.getPersons = async () => {
        $scope.loading = true;
        $scope.persons = [];
        $scope.persons_pager = null;

        let depart = $scope.cboDepart == '' ? '' : $scope.cboDepart;
        let keyword = $scope.searchKey == '' ? '' : $scope.searchKey;

        $http.get(`${CONFIG.baseUrl}/persons/search?depart=${depart}&searchKey=${keyword}`)
        .then(function(res) {
            $scope.setPersons(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.getPersonsWithUrl = function(e, url, cb) {
        /** Check whether parent of clicked a tag is .disabled just do nothing */
        if ($(e.currentTarget).parent().is('li.disabled')) return;

        $scope.loading = true;
        $scope.persons = [];
        $scope.persons_pager = null;

        let depart = $scope.cboDepart == '' ? '' : $scope.cboDepart;
        let keyword = $scope.searchKey == '' ? '' : $scope.searchKey;

        $http.get(`${url}&depart=${depart}&searchKey=${keyword}`)
        .then(function(res) {
            cb(res);

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.setPersons = function(res) {
        const { data, ...pager } = res.data.persons;

        $scope.persons = data;
        $scope.persons_pager = pager;
    };

    $scope.onSelectedPerson = (mode, person) => {
        if (person) {
            $scope.project.owner_detail = person.prefix.prefix_name + person.person_firstname +' '+ person.person_lastname + ' โทร.' + person.person_tel;
            $scope.project.owner_person = person.person_id;
        }

        $('#persons-list').modal('hide');
        $scope.selectedMode = '';
    };

    $scope.getById = function(id, cb) {
        $http.get(`${CONFIG.baseUrl}/assets/get-ajax-byid/${id}`)
        .then(function(res) {
            cb(res.data.plan);
        }, function(err) {
            console.log(err);
        });
    }

    $scope.setEditControls = function(plan) {
        /** Global data */
        $scope.planId                   = plan.id;
        $scope.planType                 = 1;

        /** ข้อมูลครุภัณฑ์ */
        $scope.asset.asset_id           = plan.id;
        $scope.asset.in_plan            = plan.in_plan;
        $scope.asset.year               = plan.year;
        // $scope.asset.plan_no            = plan.plan_no;
        $scope.asset.desc               = plan.plan_item.item.item_name;
        $scope.asset.spec               = plan.plan_item.spec;
        $scope.asset.price_per_unit     = plan.plan_item.price_per_unit;
        $scope.asset.amount             = plan.plan_item.amount;
        $scope.asset.sum_price          = plan.plan_item.sum_price;
        $scope.asset.start_month        = $scope.monthLists.find(m => m.id == plan.start_month).name;
        $scope.asset.reason             = plan.reason;
        $scope.asset.remark             = plan.remark;
        $scope.asset.status             = plan.status;

        /** Convert int value to string */
        $scope.asset.category_id        = plan.plan_item.item.category_id.toString();
        $scope.asset.unit_id            = plan.plan_item.unit_id.toString();
        $scope.asset.depart_id          = plan.depart_id.toString();
        $scope.asset.division_id        = plan.division_id ? plan.division_id.toString() : '';
        /** Convert db date to thai date. */            
        // $scope.leave.leave_date         = StringFormatService.convFromDbDate(data.leave.leave_date);
    };

    $scope.store = function(event, form) {
        event.preventDefault();

        $(`#${form}`).submit();
    }

    $scope.edit = function(id) {
        window.location.href = `${CONFIG.baseUrl}/leaves/edit/${id}`;
    };

    $scope.update = function(event) {
        event.preventDefault();
    
        if(confirm(`คุณต้องแก้ไขใบลาเลขที่ ${$scope.leave.leave_id} ใช่หรือไม่?`)) {
            $('#frmEditLeave').submit();
        }
    };

    $scope.delete = function(e, id) {
        e.preventDefault();

        if(confirm(`คุณต้องลบแผนครุภัณฑ์รหัส ${id} ใช่หรือไม่?`)) {
            $http.delete(`${CONFIG.baseUrl}/plans/${id}`)
            .then(res => {
                console.log(res);
            }, err => {
                console.log(err);
            });
        }
    };
});