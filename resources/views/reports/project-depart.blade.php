@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            รายงานแผนงาน/โครงการตามหน่วยงาน
            <!-- <small>preview of simple tables</small> -->
        </h1>

        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/reports/all') }}">รายงาน</a></li>
            <li class="breadcrumb-item active">รายงานแผนงาน/โครงการตามหน่วยงาน</li>
        </ol>
    </section>

    <!-- Main content -->
    <section
        class="content"
        ng-controller="reportCtrl"
        ng-init="
            getProjectByDepart();
            initForms({ departs: {{ $departs }} });
        "
    >

        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ค้นหาข้อมูล</h3>
                    </div>
                    <form id="frmSearch" name="frmSearch" role="form">
                        <div class="box-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>กลุ่มภารกิจ</label>
                                    <select
                                        id="cboFaction"
                                        name="cboFaction"
                                        ng-model="cboFaction"
                                        class="form-control select2"
                                        style="width: 100%; font-size: 12px;"
                                        ng-change="onSelectedFaction(cboFaction); getProjectByDepart();"
                                    >
                                        <option value="" selected="selected">-- กรุณาเลือก --</option>
                                        @foreach($factions as $faction)
                                            <option value="{{ $faction->faction_id }}">
                                                {{ $faction->faction_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- // TODO: should use datepicker instead -->
                            <div class="form-group col-md-6">
                                <label>ปีงบประมาณ</label>
                                <select
                                    id="cboYear"
                                    name="cboYear"
                                    ng-model="cboYear"
                                    class="form-control"
                                    ng-change="getProjectByDepart()"
                                >
                                    <option value="">-- ทั้งหมด --</option>
                                    <option ng-repeat="y in budgetYearRange" value="@{{ y }}">
                                        @{{ y }}
                                    </option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>สถานะ</label>
                                <select
                                    id="cboApproved"
                                    name="cboApproved"
                                    ng-model="cboApproved"
                                    class="form-control"
                                    ng-change="getProjectByDepart()"
                                >
                                    <option value="">ยังไม่อนุมัติ</option>
                                    <option value="A">อนุมัติ</option>
                                </select>
                            </div>

                        </div><!-- /.box-body -->
                    </form>
                </div><!-- /.box -->

                <div class="box">
                    <div class="box-header with-border table-striped">
                        <div class="row">
                            <div class="col-md-6">
                                <h3 class="box-title">รายงานแผนงาน/โครงการตามหน่วยงาน ปีงบประมาณ @{{ cboYear }}</h3>
                            </div>
                            <div class="col-md-6">
                                <a href="#" class="btn btn-success pull-right" ng-click="exportToExcel('#tableData')">
                                    <i class="fa fa-file-excel-o" aria-hidden="true"></i>
                                    Excel
                                </a>
                            </div>
                        </div>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table class="table table-bordered table-striped" id="tableData">
                            <thead>
                                <tr>
                                    <th style="width: 3%; text-align: center;" rowspan="2">#</th>
                                    <th style="text-align: left;" rowspan="2">หน่วยงาน</th>
                                    <th style="text-align: center;" colspan="2">โรงพยาบาล</th>
                                    <th style="text-align: center;" colspan="2">CUP</th>
                                    <th style="text-align: center;" colspan="2">ตำบล</th>
                                    <th style="text-align: center;" colspan="2">รวม</th>
                                </tr>
                                <tr>
                                    <th style="width: 6%; text-align: center;">จำนวน</th>
                                    <th style="width: 8%; text-align: center;">งบประมาณ</th>
                                    <th style="width: 6%; text-align: center;">จำนวน</th>
                                    <th style="width: 8%; text-align: center;">งบประมาณ</th>
                                    <th style="width: 6%; text-align: center;">จำนวน</th>
                                    <th style="width: 8%; text-align: center;">งบประมาณ</th>
                                    <th style="width: 6%; text-align: center;">จำนวน</th>
                                    <th style="width: 8%; text-align: center;">งบประมาณ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="(index, project) in projects">
                                    <td style="text-align: center;">@{{ index+1 }}</td>
                                    <td>
                                        @{{ project.depart_name }}
                                    </td>
                                    <td style="text-align: center;">@{{ project.hos_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ project.hos_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ project.cup_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ project.cup_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ project.tam_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ project.tam_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ project.total_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ project.total_budget | currency:'':0 }}</td>
                                </tr>
                                
                                <tr style="font-weight: bold;">
                                    <td style="text-align: center;" colspan="2">รวม</td>
                                    <td style="text-align: center;">@{{ totalByDepart.hos_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ totalByDepart.hos_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ totalByDepart.cup_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ totalByDepart.cup_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ totalByDepart.tam_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ totalByDepart.tam_budget | currency:'':0 }}</td>
                                    <td style="text-align: center;">@{{ totalByDepart.total_amount | currency:'':0 }}</td>
                                    <td style="text-align: right;">@{{ totalByDepart.total_budget | currency:'':0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div><!-- /.box-body -->
                    <div class="box-footer clearfix" ng-show="false">
                        <div class="row">
                            <div class="col-md-4">
                                หน้า @{{ pager.current_page }} จาก @{{ pager.last_page }}
                            </div>
                            <div class="col-md-4" style="text-align: center;">
                                จำนวน @{{ pager.total }} รายการ
                            </div>
                            <div class="col-md-4">
                                <ul class="pagination pagination-sm no-margin pull-right">
                                    <li ng-if="pager.current_page !== 1">
                                        <a ng-click="getDataWithURL(pager.path+ '?page=1')" aria-label="Previous">
                                            <span aria-hidden="true">First</span>
                                        </a>
                                    </li>
                                
                                    <li ng-class="{'disabled': (pager.current_page==1)}">
                                        <a ng-click="getDataWithURL(pager.prev_page_url)" aria-label="Prev">
                                            <span aria-hidden="true">Prev</span>
                                        </a>
                                    </li>
        
                                    <!-- <li ng-if="pager.current_page < pager.last_page && (pager.last_page - pager.current_page) > 10">
                                        <a href="@{{ pager.url(pager.current_page + 10) }}">
                                            ...
                                        </a>
                                    </li> -->
                                
                                    <li ng-class="{'disabled': (pager.current_page==pager.last_page)}">
                                        <a ng-click="getDataWithURL(pager.next_page_url)" aria-label="Next">
                                            <span aria-hidden="true">Next</span>
                                        </a>
                                    </li>
        
                                    <li ng-if="pager.current_page !== pager.last_page">
                                        <a ng-click="getDataWithURL(pager.path+ '?page=' +pager.last_page)" aria-label="Previous">
                                            <span aria-hidden="true">Last</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div><!-- /.box-footer -->

                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->

    </section>

    <script>
        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()
        });
    </script>

@endsection