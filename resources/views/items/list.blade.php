@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            สินค้า/บริการ
            <!-- <small>preview of simple tables</small> -->
        </h1>

        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">สินค้า/บริการ</li>
        </ol>
    </section>

    <!-- Main content -->
    <section
        class="content"
        ng-controller="itemCtrl"
        ng-init="
            getAll();
            initForms({
                planTypes: {{ $planTypes }},
                categories: {{ $categories }},
                groups: {{ $groups }}
            }, 1);"
    >

        <div class="row">
            <div class="col-md-12">

                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title">ค้นหาข้อมูล</h3>
                    </div>

                    <form id="frmSearch" name="frmSearch" role="form">
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label>ประเภทแผน</label>
                                    <select
                                        id="cboPlanType"
                                        name="cboPlanType"
                                        class="form-control"
                                        ng-model="cboPlanType"
                                        ng-change="onPlanTypeSelected(cboPlanType); getAll($event)"
                                    >
                                        <option value="">-- ทั้งหมด --</option>
                                        @foreach($planTypes as $type)
                                            <option value="{{ $type->id }}">
                                                {{ $type->plan_type_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>ประเภทสินค้า/บริการ</label>
                                    <select
                                        id="cboCategory"
                                        name="cboCategory"
                                        ng-model="cboCategory"
                                        class="form-control"
                                        ng-change="onCategorySelected(cboCategory); getAll($event);"
                                    >
                                        <option value="">-- ทั้งหมด --</option>
                                        <option ng-repeat="category in forms.categories" value="@{{ category.id }}">
                                            @{{ category.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>กลุ่มสินค้า/บริการ</label>
                                    <select
                                        id="cboGroup"
                                        name="cboGroup"
                                        ng-model="cboGroup"
                                        class="form-control"
                                        ng-change="getAll($event)"
                                    >
                                        <option value="">-- ทั้งหมด --</option>
                                        <option ng-repeat="group in forms.groups" value="@{{ group.id }}">
                                            @{{ group.name }}
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>ชื่อสินค้า/บริการ</label>
                                    <input
                                        id="txtItemName"
                                        name="txtItemName"
                                        class="form-control"
                                        ng-model="txtItemName"
                                        ng-keyup="getAll($event)"
                                    />
                                </div>
                            </div>
                        </div><!-- /.box-body -->
                    </form>
                </div><!-- /.box -->

                <div class="box">
                    <div class="box-header with-border">
                        <div class="row" style="display: flex; align-items: center;">
                            <div class="col-md-6">
                                <h3 class="box-title">สินค้า/บริการ</h3>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ url('/items/add') }}" class="btn btn-primary pull-right">
                                    เพิ่มรายการ
                                </a>
                            </div>
                        </div>
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
                                    <th style="width: 10%; text-align: center;">รหัสครุภัณฑ์</th>
                                    <th style="width: 15%; text-align: center;">ประเภทสินค้า/บริการ</th>
                                    <th>รายการ</th>
                                    <th style="width: 10%; text-align: center;">ราคาต่อหน่วย</th>
                                    <th style="width: 8%; text-align: center;">หน่วย</th>
                                    <th style="width: 8%; text-align: center;" ng-show="cboPlanType == 2">
                                        ใน/นอกคลัง
                                    </th>
                                    <!-- <th style="width: 8%; text-align: center;">ซื้อปีแรก</th> -->
                                    <th style="width: 10%; text-align: center;">Fix cost</th>
                                    <th style="width: 10%; text-align: center;">หมายเหตุ</th>
                                    <th style="width: 10%; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="(index, item) in items">
                                    <td style="text-align: center;">@{{ index+pager.from }}</td>
                                    <td style="text-align: center;">@{{ item.asset_no }}</td>
                                    <td style="text-align: center;">
                                        @{{ item.category.name }}
                                        <p class="description" ng-show="item.group">
                                            (กลุ่ม@{{ item.group.name }})
                                        </p>
                                    </td>
                                    <td>
                                        @{{ item.item_name }}
                                        <span ng-show="item.en_name">(@{{ item.en_name }})</span>
                                    </td>
                                    <td style="text-align: center;">
                                        @{{ item.price_per_unit | currency:'':2 }}
                                    </td>
                                    <td style="text-align: center;">
                                        @{{ item.unit.name }}
                                    </td>
                                    <td style="text-align: center;" ng-show="cboPlanType == 2">
                                        <span ng-show="item.in_stock == 1">ในคลัง</span>
                                        <span ng-show="item.in_stock == 0">นอกคลัง</span>
                                    </td>
                                    <td style="text-align: center;">
                                        <i class="fa fa-check-square-o text-success" aria-hidden="true" ng-show="item.is_fixcost == '1'"></i>
                                    </td>
                                    <td style="text-align: center; font-size: 12px;">
                                        @{{ item.remark }}
                                    </td>
                                    <td style="text-align: center;">
                                        <div style="display: flex; justify-content: center; gap: 2px;">
                                            <a  href="{{ url('/items/detail') }}/@{{ item.id }}"
                                                class="btn btn-primary btn-xs" 
                                                title="รายละเอียด">
                                                <i class="fa fa-search"></i>
                                            </a>
                                            <a  ng-click="edit(item.id)"
                                                class="btn btn-warning btn-xs"
                                                title="แก้ไขรายการ">
                                                <i class="fa fa-edit"></i>
                                            </a>
                                            <form
                                                id="frmDelete"
                                                method="POST"
                                                action="{{ url('/items/delete') }}"
                                            >
                                                {{ csrf_field() }}
                                                <button
                                                    type="submit"
                                                    ng-click="delete($event, item.id)"
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
                                        <a href="#" ng-click="getItemsWithUrl($event, pager.path+ '?page=1', setItems)" aria-label="Previous">
                                            <span aria-hidden="true">First</span>
                                        </a>
                                    </li>
                                
                                    <li ng-class="{'disabled': (pager.current_page==1)}">
                                        <a href="#" ng-click="getItemsWithUrl($event, pager.prev_page_url, setItems)" aria-label="Prev">
                                            <span aria-hidden="true">Prev</span>
                                        </a>
                                    </li>

                                    <!-- <li ng-repeat="i in debtPages" ng-class="{'active': pager.current_page==i}">
                                        <a href="#" ng-click="getItemsWithUrl(pager.path + '?page=' +i)">
                                            @{{ i }}
                                        </a>
                                    </li> -->

                                    <!-- <li ng-if="pager.current_page < pager.last_page && (pager.last_page - pager.current_page) > 10">
                                        <a href="#" ng-click="pager.path">
                                            ...
                                        </a>
                                    </li> -->

                                    <li ng-class="{'disabled': (pager.current_page==pager.last_page)}">
                                        <a href="#" ng-click="getItemsWithUrl($event, pager.next_page_url, setItems)" aria-label="Next">
                                            <span aria-hidden="true">Next</span>
                                        </a>
                                    </li>

                                    <li ng-if="pager.current_page !== pager.last_page">
                                        <a href="#" ng-click="getItemsWithUrl($event, pager.path+ '?page=' +pager.last_page, setItems)" aria-label="Previous">
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
        });
    </script>

@endsection