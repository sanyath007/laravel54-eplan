@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            รายละเอียดแผนครุภัณฑ์ : เลขที่ ({{ $plan->plan_no }})
            <!-- <small>preview of simple tables</small> -->
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">รายละเอียดแผนครุภัณฑ์</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content" ng-controller="planAssetCtrl" ng-init="getById({{ $plan->id }}, setEditControls);">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">รายละเอียดแผนครุภัณฑ์</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group col-md-6">
                                    <label>ปีงบ :</label>
                                    <input type="text"
                                            id="year" 
                                            name="year"
                                            ng-model="asset.year"
                                            class="form-control">
                                    </inp>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>ประเภทครุภัณฑ์ :</label>
                                    <select id="category_id"
                                            name="category_id"
                                            ng-model="asset.category_id"
                                            class="form-control">
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>รายการ :</label>
                                    <input
                                        type="text"
                                        ng-model="asset.desc"
                                        class="form-control" />
                                </div>

                                <div class="form-group col-md-6">
                                    <label>รายละเอียด (Spec.) :</label>
                                    <input  type="text"
                                            id="spec"
                                            name="spec"
                                            ng-model="asset.spec"
                                            class="form-control">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>ราคาต่อหน่วย :</label>
                                    <input  type="text"
                                            id="price_per_unit"
                                            name="price_per_unit"
                                            ng-model="asset.price_per_unit"
                                            class="form-control" />
                                </div>

                                <div class="form-group col-md-6">
                                    <label>จำนวน :</label>
                                    <div style="display: flex; gap: 5px;">
                                        <input  type="text"
                                                id="amount"
                                                name="amount"
                                                ng-model="asset.amount"
                                                class="form-control" />

                                        <select id="unit_id"
                                                name="unit_id"
                                                ng-model="asset.unit_id"
                                                class="form-control">
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>กลุ่มงาน :</label>
                                    <select id="depart_id"
                                            name="depart_id"
                                            ng-model="asset.depart_id"
                                            class="form-control">
                                            @foreach($departs as $depart)
                                                <option value="{{ $depart->depart_id }}">
                                                    {{ $depart->depart_name }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>งาน :</label>
                                    <select id="division_id"
                                            name="division_id"
                                            ng-model="asset.division_id"
                                            class="form-control">
                                            @foreach($divisions as $division)
                                                <option value="{{ $division->ward_id }}">
                                                    {{ $division->ward_name }}
                                                </option>
                                            @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>เหตุผล :</label>
                                    <textarea
                                        id="reason" 
                                        name="reason" 
                                        ng-model="asset.reason" 
                                        class="form-control"
                                    ></textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>หมายเหตุ :</label>
                                    <textarea
                                        id="remark" 
                                        name="remark" 
                                        ng-model="asset.remark" 
                                        class="form-control"
                                    ></textarea>
                                </div>
                                
                                
                                <div class="col-md-12" style="margin-bottom: 15px;" ng-show="asset.attachment">
                                    <label>เอกสารแนบ :</label>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-start;">
                                        <a  href="{{ url('/'). '/uploads/' }}@{{ asset.attachment }}"
                                            title="ไฟล์แนบ"
                                            target="_blank">
                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                            @{{ asset.attachment }}
                                        </a>

                                        <span style="margin-left: 10px;">
                                            <a href="#">
                                                <span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>
                                            </a>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>เริ่มเดือน :</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input
                                            type="text"
                                            value="@{{ asset.start_month }}"
                                            class="form-control pull-right"
                                        />
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>สถานะ :</label>
                                    <div style="border: 1px solid #d2d6de; height: 34px; display: flex; align-items: center; padding: 0 5px;">
                                        <span class="label label-primary" ng-show="asset.status == 0">
                                            @{{ asset.status }} อยู่ระหว่างดำเนินการ
                                        </span>
                                        <span class="label label-info" ng-show="asset.status == 1">
                                            @{{ asset.status }} ส่งเอกสารแล้ว
                                        </span>
                                        <span class="label bg-navy" ng-show="asset.status == 2">
                                            @{{ asset.status }} รับเอกสารแล้ว
                                        </span>
                                        <span class="label label-success" ng-show="asset.status == 3">
                                            @{{ asset.status }} ออกใบสั้งซื้อแล้ว
                                        </span>
                                        <span class="label bg-maroon" ng-show="asset.status == 4">
                                            @{{ asset.status }} ตรวจรับแล้ว
                                        </span>
                                        <span class="label label-warning" ng-show="asset.status == 5">
                                            @{{ asset.status }} ส่งเบิกเงินแล้ว
                                        </span>
                                        <span class="label label-danger" ng-show="asset.status == 6">
                                            @{{ asset.status }} ตั้งหนี้แล้ว
                                        </span>
                                        <span class="label label-default" ng-show="asset.status == 9">
                                            @{{ asset.status }} ยกเลิก
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div style="display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                                    <a
                                        href="#"
                                        class="btn btn-success"
                                        ng-show="[0].includes(asset.status)"
                                        ng-click="showSupportedForm()"
                                    >
                                        <i class="fa fa-print"></i> บันทึกขอสนับสนุน
                                    </a>
                                    <a
                                        href="#"
                                        class="btn btn-primary"
                                        ng-show="[1].includes(asset.status)"
                                        ng-click="showPoForm()"
                                    >
                                        <i class="fa fa-calculator"></i> บันทึกใบ PO
                                    </a>
                                    <a
                                        href="#"
                                        ng-click="edit(asset.asset_id)"
                                        ng-show="[0,1].includes(asset.status)"
                                        class="btn btn-warning"
                                    >
                                        <i class="fa fa-edit"></i> แก้ไข
                                    </a>
                                    <form
                                        id="frmDelete"
                                        method="POST"
                                        action="{{ url('/asset/delete') }}"
                                        ng-show="[0,1].includes(asset.status)"
                                    >
                                        <input type="hidden" id="id" name="id" value="@{{ asset.asset_id }}" />
                                        {{ csrf_field() }}
                                        <button
                                            type="submit"
                                            ng-click="delete($event, asset.asset_id)"
                                            class="btn btn-danger btn-block"
                                        >
                                            <i class="fa fa-trash"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                                <!-- /** Action buttons container */ -->

                            </div>

                            @include('shared._supported-form')
                            @include('shared._po-form')

                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->

    </section>

@endsection