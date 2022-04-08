@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            รายการอนุมัติแล้ว
            <!-- <small>preview of simple tables</small> -->
        </h1>

        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">รายการอนุมัติแล้ว</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content" ng-controller="planAssetCtrl" ng-init="getAll()">

        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ค้นหาข้อมูล</h3>
                    </div>

                    <form id="frmSearch" name="frmSearch" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>ปีงบประมาณ</label>
                                    <select
                                        id="cboYear"
                                        name="cboYear"
                                        ng-model="cboYear"
                                        class="form-control"
                                        ng-change="getAll($event)"
                                    >
                                        <option value="">-- ทั้งหมด --</option>
                                        <option ng-repeat="y in budgetYearRange" value="@{{ y }}">
                                            @{{ y }}
                                        </option>
                                    </select>
                                </div><!-- /.form group -->
                                <div class="form-group col-md-6">
                                    <label>ประเภทครุภัณฑ์</label>
                                    <select
                                        id="cboCategory"
                                        name="cboCategory"
                                        ng-model="cboCategory"
                                        class="form-control"
                                        ng-change="getAll($event)"
                                    >
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($asset_categories as $category)

                                            <option value="{{ $category->id }}">
                                                {{ $category->category_name }}
                                            </option>

                                        @endforeach
                                    </select>
                                </div><!-- /.form group -->
                                <!-- <div class="form-group">
                                    <label>คำค้นหา</label>
                                    <input
                                        type="text"
                                        id="searchKey"
                                        name="searchKey"
                                        ng-model="searchKeyword"
                                        ng-keyup="getData($event)"
                                        class="form-control">
                                </div> -->

                            </div><!-- /.row -->
                        </div><!-- /.box-body -->

                        <div class="box-footer">
                            <a href="{{ url('/assets/add') }}" class="btn btn-primary">
                                เพิ่มรายการ
                            </a>
                        </div>
                    </form>
                </div><!-- /.box -->

                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">รายการอนุมัติแล้ว</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        <table class="table table-bordered table-striped" style="font-size: 14px; margin-bottom: 10px;">
                            <thead>
                                <tr>
                                    <th style="width: 3%; text-align: center;">#</th>
                                    <th style="width: 8%; text-align: center;">เลขที่แผน</th>
                                    <!-- <th style="width: 8%; text-align: center;">ปีงบ</th> -->
                                    <th>รายการ</th>
                                    <th style="width: 8%; text-align: center;">ราคาต่อหน่วย</th>
                                    <th style="width: 8%; text-align: center;">รวมเป็นเงิน</th>
                                    <th style="width: 20%; text-align: center;">หน่วยงาน</th>
                                    <th style="width: 12%; text-align: center;">สถานะ</th>
                                    <th style="width: 10%; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="(index, asset) in assets">
                                    <td style="text-align: center;">@{{ index+pager.from }}</td>
                                    <td style="text-align: center;">@{{ asset.plan_no }}</td>
                                    <!-- <td style="text-align: center;">@{{ asset.year }}</td> -->
                                    <td>
                                        <p style="margin: 0;">@{{ asset.category.category_name }}</p>
                                        @{{ asset.desc }} จำนวน 
                                        <span>@{{ asset.amount | currency:'':0 }}</span>
                                        <span>@{{ asset.unit.name }}</span>
                                        <a  href="{{ url('/'). '/uploads/' }}@{{ asset.attachment }}"
                                            class="btn btn-default btn-xs" 
                                            title="ไฟล์แนบ"
                                            target="_blank"
                                            ng-show="asset.attachment">
                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                        </a>
                                    </td>
                                    <td style="text-align: center;">
                                        @{{ asset.price_per_unit | currency:'':0 }}
                                    </td>
                                    <td style="text-align: center;">
                                        @{{ asset.sum_price | currency:'':0 }}
                                    </td>
                                    <td style="text-align: center;">
                                        <p style="margin: 0;">@{{ asset.depart.depart_name }}</p>
                                        <p style="margin: 0;">@{{ asset.division.ward_name }}</p>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="label label-primary" ng-show="asset.status == 0">
                                            อยู่ระหว่างดำเนินการ
                                        </span>
                                        <span class="label label-info" ng-show="asset.status == 1">
                                            อนุมัติแล้ว
                                        </span>
                                        <span class="label label-info" ng-show="asset.status == 2">
                                            รับเอกสารแล้ว
                                        </span>
                                        <span class="label label-success" ng-show="asset.status == 3">
                                            ออกใบ PO แล้ว
                                        </span>
                                        <span class="label label-default" ng-show="asset.status == 4">
                                            ไม่ผ่านการอนุมัติ
                                        </span>
                                        <span class="label label-danger" ng-show="asset.status == 9">
                                            ยกเลิก
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; justify-content: center; gap: 2px;">
                                            <a  href="{{ url('/assets/detail') }}/@{{ asset.id }}"
                                                class="btn btn-primary btn-xs" 
                                                title="รายละเอียด">
                                                <i class="fa fa-search"></i>
                                            </a>
                                            <a  ng-click="edit(asset.id)"
                                                ng-show="asset.status == 0 || (asset.status == 1 && {{ Auth::user()->memberOf->duty_id }} == 2)"
                                                class="btn btn-warning btn-xs"
                                                title="แก้ไขรายการ">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form
                                                id="frmDelete"
                                                method="POST"
                                                action="{{ url('/assets/delete') }}"
                                                ng-show="asset.status == 0 || (asset.status == 1 && {{ Auth::user()->memberOf->duty_id }} == 2)"
                                            >
                                                {{ csrf_field() }}
                                                <button
                                                    type="submit"
                                                    ng-click="delete($event, asset.id)"
                                                    class="btn btn-danger btn-xs"
                                                >
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>             
                                </tr>
                            </tbody>
                        </table>

                        <div class="row">
                            <div class="col-md-4">
                                หน้า @{{ pager.current_page }} จาก @{{ pager.last_page }}
                            </div>
                            <div class="col-md-4" style="text-align: center;">
                                จำนวน @{{ pager.total }} รายการ
                            </div>
                            <div class="col-md-4">
                                <ul class="pagination pagination-sm no-margin pull-right" ng-show="pager.last_page > 1">
                                    <li ng-if="pager.current_page !== 1">
                                        <a href="#" ng-click="getDataWithURL($event, pager.path+ '?page=1', setLeaves)" aria-label="Previous">
                                            <span aria-hidden="true">First</span>
                                        </a>
                                    </li>
                                
                                    <li ng-class="{'disabled': (pager.current_page==1)}">
                                        <a href="#" ng-click="getDataWithURL($event, pager.prev_page_url, setLeaves)" aria-label="Prev">
                                            <span aria-hidden="true">Prev</span>
                                        </a>
                                    </li>

                                    <!-- <li ng-repeat="i in debtPages" ng-class="{'active': pager.current_page==i}">
                                        <a href="#" ng-click="getDataWithURL(pager.path + '?page=' +i)">
                                            @{{ i }}
                                        </a>
                                    </li> -->

                                    <!-- <li ng-if="pager.current_page < pager.last_page && (pager.last_page - pager.current_page) > 10">
                                        <a href="#" ng-click="pager.path">
                                            ...
                                        </a>
                                    </li> -->

                                    <li ng-class="{'disabled': (pager.current_page==pager.last_page)}">
                                        <a href="#" ng-click="getDataWithURL($event, pager.next_page_url, setLeaves)" aria-label="Next">
                                            <span aria-hidden="true">Next</span>
                                        </a>
                                    </li>

                                    <li ng-if="pager.current_page !== pager.last_page">
                                        <a href="#" ng-click="getDataWithURL($event, pager.path+ '?page=' +pager.last_page, setLeaves)" aria-label="Previous">
                                            <span aria-hidden="true">Last</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->

                    <!-- Loading (remove the following to stop the loading)-->
                    <div ng-show="loading" class="overlay">
                        <i class="fa fa-refresh fa-spin"></i>
                    </div>
                    <!-- end loading -->

                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->

    </section>

    <script>
        $(function () {
            //Initialize Select2 Elements
            $('.select2').select2()

            //Date range picker with time picker
            $('#debtDate').daterangepicker({
                timePickerIncrement: 30,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: " , ",
                }
            }, function(e) {
                console.log(e);
            });
        });
    </script>

@endsection