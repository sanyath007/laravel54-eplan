<div class="box box-info" ng-init="getProjectTypeRatio()">
    <div class="box-header">
        <h3 class="box-title">
            สัดส่วนสัดส่วนแผนงาน/โครงการ
            <!-- <span>ประจำเดือน</span> -->
        </h3>
    </div>
    <div class="box-body">

        <div id="projectPieChartContainer" style="width: 100%; height: 360px; margin: 0 auto; margin-top: 20px;"></div>

        <!-- Loading (remove the following to stop the loading)-->
        <div ng-show="loading" class="overlay">
            <i class="fa fa-refresh fa-spin"></i>
        </div>
        <!-- end loading -->

    </div><!-- /.box-body -->
</div><!-- /.box -->
