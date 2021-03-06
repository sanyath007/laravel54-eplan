app.controller('homeCtrl', function(CONFIG, $scope, $http, StringFormatService, ChartService) {
/** ################################################################################## */
    $scope.loading = false;
    $scope.pieOptions = {};
    $scope.barOptions = {};
    $scope.headLeaves = [];
    $scope.pager = null;
    $scope.departs = [];
    $scope.departPager = null;

    $('#cboAssetDate').datepicker({
        autoclose: true,
        format: 'mm/yyyy',
        viewMode: "months", 
        minViewMode: "months",
        language: 'th',
        thaiyear: true
    })
    .datepicker('update', moment().toDate())
    .on('changeDate', function(event) {
        $scope.getDepartLeaves();
    });

    $('#cboMaterialDate').datepicker({
        autoclose: true,
        format: 'mm/yyyy',
        viewMode: "months", 
        minViewMode: "months",
        language: 'th',
        thaiyear: true
    })
    .datepicker('update', moment().toDate())
    .on('changeDate', function(event) {
        $scope.getHeadLeaves();
    });

    $scope.assets = [];
    $scope.totalAsset = 0;
    $scope.getSummaryAssets = function() {
        $scope.loading = true;

        // let date = $('#cboAssetDate').val() !== ''
        //             ? StringFormatService.convToDbDate($('#cboAssetDate').val())
        //             : moment().format('YYYY-MM-DD');
        let year = 2565

        $http.get(`${CONFIG.apiUrl}/dashboard/summary-assets?year=${year}`)
        .then(function(res) {
            const { plans, budget, categories } = res.data;

            let cates = categories.map(cate => {
                const summary = budget.find(bud => bud.expense_id === cate.expense_id);
                cate.budget = summary ? summary.budget : 0;

                return cate;
            });

            $scope.assets = plans.map(plan => {
                const cateInfo = cates.find(cate => cate.id === plan.category_id);
                plan.category_name = cateInfo.name;
                plan.budget = cateInfo.budget;

                return plan;
            });

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.materials = [];
    $scope.totalMaterial = 0;
    $scope.getSummaryMaterials = function() {
        $scope.loading = true;

        // let date = $('#cboAssetDate').val() !== ''
        //             ? StringFormatService.convToDbDate($('#cboAssetDate').val())
        //             : moment().format('YYYY-MM-DD');
        let year = 2565

        $http.get(`${CONFIG.apiUrl}/dashboard/summary-materials?year=${year}`)
        .then(function(res) {
            const { plans, budget, categories } = res.data;

            let cates = categories.map(cate => {
                const summary = budget.find(bud => bud.expense_id === cate.expense_id);
                cate.budget = summary ? summary.budget : 0;

                return cate;
            });

            $scope.materials = plans.map(plan => {
                const cateInfo = cates.find(cate => cate.id === plan.category_id);
                plan.category_name = cateInfo.name;
                plan.budget = cateInfo.budget;

                return plan;
            });

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.stat1Cards = [];
    $scope.stat2Cards = [];
    $scope.getStat1 = function () {
        $scope.loading = true;

        let year = '2565';

        $http.get(`${CONFIG.baseUrl}/dashboard/stat1/${year}`)
        .then(function(res) {
            $scope.stat1Cards = res.data.stats;

            $scope.loading = false;
        }, function(err) {
            console.log(err);

            $scope.loading = false;
        });
    };

    $scope.getStat2 = function () {
        $scope.loading = true;

        let year = '2565';

        $http.get(`${CONFIG.baseUrl}/dashboard/stat2/${year}`)
        .then(function(res) {
            $scope.stat2Cards = res.data.stats;

            $scope.getPlanTypeRatio(res.data.stats);

            $scope.loading = false;
        }, function(err) {
            console.log(err);

            $scope.loading = false;
        });
    };

    // TODO: Duplicated method
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

    $scope.orders = [];
    $scope.orders_pager = null;
    $scope.getLatestOrders = function() {
        $scope.loading = true;
        $scope.orders = [];
        $scope.orders_pager = null;

        let year = $scope.cboYear === '' ? '' : $scope.cboYear;
        let status = $scope.cboStatus === '' ? '' : $scope.cboStatus;

        $http.get(`${CONFIG.baseUrl}/orders/search?year=${year}&status=0&last=5`)
        .then(function(res) {
            const { data, ...pager } = res.data.orders;

            $scope.orders = data;
            $scope.orders_pager = pager;

            $scope.loading = false;
        }, function(err) {
            console.log(err);
            $scope.loading = false;
        });
    };

    $scope.getPlanTypeRatio = function (data) {
        $scope.pieOptions = ChartService.initPieChart("pieChartContainer", "????????????????????????????????????????????????????????????????????????", "?????????", "???????????????????????????????????????????????????");
        $scope.pieOptions.series[0].data.push({ name: '???????????????????????????', y: parseInt(data[0].sum_all) });
        $scope.pieOptions.series[0].data.push({ name: '????????????????????????????????????', y: parseInt(data[1].sum_all) });
        $scope.pieOptions.series[0].data.push({ name: '??????????????????????????????', y: parseInt(data[2].sum_all) });
        $scope.pieOptions.series[0].data.push({ name: '????????????????????????', y: parseInt(data[3].sum_all) });

        var chart = new Highcharts.Chart($scope.pieOptions);
    };

    $scope.getSumMonthData = function () {
        var month = '2018';
        console.log(month);

        ReportService.getSeriesData('/report/sum-month-chart/', month)
        .then(function(res) {
            console.log(res);
            var debtSeries = [];
            var paidSeries = [];
            var setzeroSeries = [];

            angular.forEach(res.data, function(value, key) {
                let debt = (value.debt) ? parseFloat(value.debt.toFixed(2)) : 0;
                let paid = (value.paid) ? parseFloat(value.paid.toFixed(2)) : 0;
                let setzero = (value.setzero) ? parseFloat(value.setzero.toFixed(2)) : 0;

                debtSeries.push(debt);
                paidSeries.push(paid);
                setzeroSeries.push(setzero);
            });

            var categories = ['??????', '??????', '??????', '??????', '??????', '?????????', '?????????', '??????', '?????????', '??????', '??????', '??????']
            $scope.barOptions = ReportService.initBarChart("barContainer1", "???????????????????????????????????????????????????????????? ???????????? 2561", categories, '???????????????');
            $scope.barOptions.series.push({
                name: '?????????????????????????????????',
                data: debtSeries
            }, {
                name: '????????????????????????',
                data: paidSeries
            }, {
                name: '?????????????????????????????????',
                data: setzeroSeries
            });

            var chart = new Highcharts.Chart($scope.barOptions);
        }, function(err) {
            console.log(err);
        });
    };

    $scope.getSumYearData = function () {       
        var month = '2018';
        console.log(month);

        ReportService.getSeriesData('/report/sum-year-chart/', month)
        .then(function(res) {
            console.log(res);
            var debtSeries = [];
            var paidSeries = [];
            var setzeroSeries = [];
            var categories = [];

            angular.forEach(res.data, function(value, key) {
                let debt = (value.debt) ? parseFloat(value.debt.toFixed(2)) : 0;
                let paid = (value.paid) ? parseFloat(value.paid.toFixed(2)) : 0;
                let setzero = (value.setzero) ? parseFloat(value.setzero.toFixed(2)) : 0;

                categories.push(parseInt(value.yyyy) + 543);
                debtSeries.push(debt);
                paidSeries.push(paid);
                setzeroSeries.push(setzero);
            });

            $scope.barOptions = ReportService.initBarChart("barContainer2", "??????????????????????????????????????????????????????", categories, '???????????????');
            $scope.barOptions.series.push({
                name: '?????????????????????????????????',
                data: debtSeries
            }, {
                name: '????????????????????????',
                data: paidSeries
            }, {
                name: '?????????????????????????????????',
                data: setzeroSeries
            });

            var chart = new Highcharts.Chart($scope.barOptions);
        }, function(err) {
            console.log(err);
        });
    };

    $scope.getPeriodData = function () {
        var selectMonth = document.getElementById('selectMonth').value;
        var month = (selectMonth == '') ? moment().format('YYYY-MM') : selectMonth;
        console.log(month);

        ReportService.getSeriesData('/report/period-chart/', month)
        .then(function(res) {
            console.log(res);
            
            var categories = [];
            var nSeries = [];
            var mSeries = [];
            var aSeries = [];
            var eSeries = [];

            angular.forEach(res.data, function(value, key) {
                categories.push(value.d);
                nSeries.push(value.n);
                mSeries.push(value.m);
                aSeries.push(value.a);
                eSeries.push(value.e);
            });

            $scope.barOptions = ReportService.initStackChart("barContainer", "?????????????????????????????????????????????????????? ?????????????????????????????????", categories, '???????????????????????????????????????????????????');
            $scope.barOptions.series.push({
                name: '00.00-08.00???.',
                data: nSeries
            }, {
                name: '08.00-12.00???.',
                data: mSeries
            }, {
                name: '12.00-16.00???.',
                data: aSeries
            }, {
                name: '16.00-00.00???.',
                data: eSeries
            });

            var chart = new Highcharts.Chart($scope.barOptions);
        }, function(err) {
            console.log(err);
        });
    };
});