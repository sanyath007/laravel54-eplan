@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            ติดตามพัสดุ
            <!-- <small>preview of simple tables</small> -->
        </h1>

        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">ติดตามพัสดุ</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content" ng-controller="supportCtrl" ng-init="getAll()">

        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">ติดตามพัสดุ</h3>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <input
                            type="hidden"
                            id="user"
                            name="user"
                            value="{{ Auth::user()->person_id }}"
                        />
                        <input
                            type="hidden"
                            id="depart"
                            name="depart"
                            value="{{ Auth::user()->memberOf->depart_id }}"
                        />

                        <div class="row">
                            <div class="col-md-8">
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 4%; text-align: center;">#</th>
                                            <th style="width: 30%;">บันทึกขอสนับสนุน</th>
                                            <th>รายการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="(index, support) in supports">
                                            <td style="text-align: center;">@{{ index+1 }}</td>
                                            <td>
                                                <p style="margin: 2px;">เลขที่ @{{ support.doc_no }}</p>
                                                <p style="margin: 2px;">ลวท. @{{ support.doc_date | thdate }}</p>
                                            </td>
                                            <td>
                                                <ul style="margin: 0 5px; padding: 0 10px;">
                                                    <li ng-repeat="(index, detail) in support.details" style="margin: 5px 0;">
                                                        <span>@{{ detail.plan.plan_no }}</span>
                                                        <span>@{{ detail.plan.plan_item.item.item_name }} จำนวน </span>
                                                        <span>@{{ detail.plan.plan_item.amount | currency:'':0 }}</span>
                                                        <span>@{{ detail.plan.plan_item.unit.name }}</span>
                                                        <a href="#" class="text-aqua" ng-click="onShowTimeline(detail)">
                                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                                        </a>
                                                    </li>
                                                </ul>
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
                                                <a href="#" ng-click="getDataWithUrl($event, pager.path+ '?page=1', setSupports)" aria-label="Previous">
                                                    <span aria-hidden="true">First</span>
                                                </a>
                                            </li>
                                        
                                            <li ng-class="{'disabled': (pager.current_page==1)}">
                                                <a href="#" ng-click="getDataWithUrl($event, pager.prev_page_url, setSupports)" aria-label="Prev">
                                                    <span aria-hidden="true">Prev</span>
                                                </a>
                                            </li>

                                            <!-- <li ng-repeat="i in debtPages" ng-class="{'active': pager.current_page==i}">
                                                <a href="#" ng-click="getDataWithUrl($event, pager.path + '?page=' +i, setSupports)">
                                                    @{{ i }}
                                                </a>
                                            </li> -->

                                            <!-- <li ng-if="pager.current_page < pager.last_page && (pager.last_page - pager.current_page) > 10">
                                                <a href="#" ng-click="pager.path">
                                                    ...
                                                </a>
                                            </li> -->

                                            <li ng-class="{'disabled': (pager.current_page==pager.last_page)}">
                                                <a href="#" ng-click="getDataWithUrl($event, pager.next_page_url, setSupports)" aria-label="Next">
                                                    <span aria-hidden="true">Next</span>
                                                </a>
                                            </li>

                                            <li ng-if="pager.current_page !== pager.last_page">
                                                <a href="#" ng-click="getDataWithUrl($event, pager.path+ '?page=' +pager.last_page, setSupports)" aria-label="Previous">
                                                    <span aria-hidden="true">Last</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div><!-- /.row -->
                            </div>

                            <div class="col-md-4" ng-show="showTimeline">
                                <div>
                                    <h4 style="margin: 0;">
                                        เลขที่แผน 
                                        <span>@{{ timelinePlan.plan.plan_no }}</span>
                                    </h4>
                                    <p style="margin: 5px 0;">
                                        รายการ
                                        <span>@{{ timelinePlan.plan.plan_item.item.item_name }}</span>
                                    </p>
                                </div>
                                <ul class="timeline timeline-inverse">
                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 1">
                                        <span class="bg-red">
                                            @{{ timelinePlan.sent_date | thdate }}
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 1">
                                        <i class="fa fa-envelope bg-blue"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                ส่งบันทึกขอสนับสนุนแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                            <!-- <div class="timeline-footer">
                                                <a class="btn btn-primary btn-xs">Read more</a>
                                                <a class="btn btn-danger btn-xs">Delete</a>
                                            </div> -->
                                        </div>
                                    </li>

                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 2">
                                        <span class="bg-red">
                                            @{{ timelinePlan.received_date | thdate }}
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 2">
                                        <i class="fa fa-pencil-square-o bg-yellow"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                รับเอกสารขอสนับสนุนแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 3">
                                        <span class="bg-red">
                                            @{{ timelinePlan.po_date | thdate }}
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 3">
                                        <i class="fa fa-cart-plus bg-purple"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                ออกใบสั่งซื้อแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 4">
                                        <span class="bg-red">
                                            ...
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 4">
                                        <i class="fa fa-check-square-o bg-green"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                ตรวจรับแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 5">
                                        <span class="bg-red">
                                            ...
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 5">
                                        <i class="fa fa-paper-plane bg-aqua"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                ส่งเบิกเงินแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="time-label" ng-show="timelinePlan.status >= 6">
                                        <span class="bg-red">
                                            ...
                                        </span>
                                    </li> -->
                                    <li ng-show="timelinePlan.status >= 6">
                                        <i class="fa fa-university bg-maroon"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                อยู่ระหว่างบริหารสัญญา
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                        </div>
                                    </li>

                                    <!-- <li class="time-label">
                                        <span class="bg-red">
                                            ...
                                        </span>
                                    </li>
                                    <li>
                                        <i class="fa fa-money bg-green"></i>
                                        <div class="timeline-item">
                                            <h3 class="timeline-header">
                                                เบิกจ่ายแล้ว
                                            </h3>
                                            <div class="timeline-body">
                                                ...
                                            </div>
                                            <div class="timeline-footer">
                                                <a class="btn btn-primary btn-xs">Read more</a>
                                                <a class="btn btn-danger btn-xs">Delete</a>
                                            </div>
                                        </div>
                                    </li> -->

                                    <li>
                                        <i class="fa fa-clock-o bg-gray"></i>
                                    </li>
                                </ul>
                            </div>
                        </div>
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
            $('.select2').select2();
        });
    </script>

@endsection