@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            รายละเอียดแผนวัสดุ
            <!-- <small>preview of simple tables</small> -->
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">รายละเอียดแผนวัสดุ</li>
        </ol>
    </section>

    <!-- Main content -->
    <section
        class="content"
        ng-controller="planMaterialCtrl"
        ng-init="
            initForms({
                departs: {{ $departs }},
                divisions: {{ $divisions }}
            }, 2);
            getById({{ $plan->id }}, setEditControls);
        "
    >

        <div class="row">
            <div class="col-md-12">

                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">
                            รายละเอียดแผนวัสดุ
                            <span ng-show="{{ $plan->plan_no }}"> : เลขที่ ({{ $plan->plan_no }})</span>
                        </h3>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group col-md-6">
                                    <label>ในแผน/นอกแผน :</label>
                                    <div class="form-control checkbox-groups">
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="in_plan"
                                                    name="in_plan"
                                                    value="I"
                                                    ng-model="material.in_plan"> ในแผน
                                        </div>
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="in_plan"
                                                    name="in_plan"
                                                    value="O"
                                                    ng-model="material.in_plan"> นอกแผน
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>ปีงบ :</label>
                                    <input type="text"
                                            id="year" 
                                            name="year"
                                            ng-model="material.year"
                                            class="form-control"
                                            tabindex="2">
                                    </inp>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>กลุ่มภารกิจ :</label>
                                    <select id="faction_id" 
                                            name="faction_id"
                                            class="form-control"
                                            ng-model="material.faction_id"
                                            ng-change="onFactionSelected(material.faction_id)">
                                        <option value="">-- เลือกกลุ่มภารกิจ --</option>
                                        @foreach($factions as $faction)
                                            <option value="{{ $faction->faction_id }}">
                                                {{ $faction->faction_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>กลุ่มงาน :</label>
                                    <select id="depart_id" 
                                            name="depart_id"
                                            ng-model="material.depart_id" 
                                            class="form-control select2"
                                            ng-change="onDepartSelected(material.depart_id)">
                                        <option value="">-- เลือกกลุ่มงาน --</option>
                                        <option ng-repeat="depart in forms.departs" value="@{{ depart.depart_id }}">
                                            @{{ depart.depart_name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>งาน :</label>
                                    <select id="division_id" 
                                            name="division_id"
                                            ng-model="material.division_id" 
                                            class="form-control select2">
                                        <option value="">-- เลือกงาน --</option>
                                        <option ng-repeat="division in forms.divisions" value="@{{ division.ward_id }}">
                                            @{{ division.ward_name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group col-md-12">
                                    <label>รายการ :</label>
                                    <input
                                        type="text"
                                        ng-model="material.desc"
                                        class="form-control"
                                        tabindex="1" />
                                </div>

                                <div class="form-group col-md-12">
                                    <label>รายละเอียด :</label>
                                    <input
                                        type="text"
                                        id="spec"
                                        name="spec"
                                        ng-model="material.spec"
                                        class="form-control">
                                </div>

                                <div class="form-group col-md-6">
                                    <label>ราคาต่อหน่วย :</label>
                                    <div class="form-control">
                                        @{{ material.price_per_unit | currency:'':2 }}
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>จำนวนที่ขอ :</label>
                                    <div style="display: flex; gap: 5px;">
                                        <input  type="text"
                                                id="amount"
                                                name="amount"
                                                ng-model="material.amount"
                                                class="form-control" />

                                        <select id="unit_id"
                                                name="unit_id"
                                                ng-model="material.unit_id"
                                                class="form-control">
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}">
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>รวมเป็นเงิน :</label>
                                    <div class="form-control">
                                        @{{ material.sum_price | currency:'':2 }}
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>สาเหตุที่ขอ :</label>
                                    <div class="form-control checkbox-groups">
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="N"
                                                    ng-model="material.request_cause"
                                                    tabindex="3"> ขอใหม่
                                        </div>
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="R"
                                                    ng-model="material.request_cause"
                                                    tabindex="3"> ทดแทน
                                        </div>
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="E"
                                                    ng-model="material.request_cause"
                                                    tabindex="3"> ขยายงาน
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>จำนวนเดิมที่มี :</label>
                                    <input  type="text"
                                            id="have_amount"
                                            name="have_amount"
                                            ng-model="material.have_amount"
                                            class="form-control" />
                                </div>

                                <div class="form-group col-md-4">
                                    <label>แหล่งเงินงบประมาณ :</label>
                                    <select
                                        id="budget_src_id"
                                        name="budget_src_id"
                                        ng-model="material.budget_src_id"
                                        class="form-control"
                                        tabindex="1"
                                    >
                                        <option value="">-- เลือกแหล่งเงินงบประมาณ --</option>
                                        @foreach($budgetSources as $budgetSource)
                                            <option value="{{ $budgetSource->id }}">{{ $budgetSource->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>ยุทธศาสตร์ :</label>
                                    <select id="strategic_id" 
                                            name="strategic_id"
                                            ng-model="material.strategic_id"
                                            ng-change="onStrategicSelected(material.strategic_id);"
                                            class="form-control"
                                            tabindex="7">
                                        <option value="">-- เลือกยุทธศาสตร์ --</option>
                                        @foreach($strategics as $strategic)
                                            <option value="{{ $strategic->id }}">
                                                {{ $strategic->strategic_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Service Plan :</label>
                                    <select id="service_plan_id" 
                                            name="service_plan_id"
                                            ng-model="material.service_plan_id"
                                            class="form-control"
                                            tabindex="7">
                                        <option value="">-- เลือก Service Plan --</option>
                                        @foreach($servicePlans as $servicePlan)
                                            <option value="{{ $servicePlan->id }}">
                                                {{ $servicePlan->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>เหตุผล :</label>
                                    <textarea
                                        id="leave_contact" 
                                        name="leave_contact" 
                                        ng-model="leave.leave_contact" 
                                        class="form-control"
                                        tabindex="17"
                                    ></textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>หมายเหตุ :</label>
                                    <textarea
                                        id="leave_contact" 
                                        name="leave_contact" 
                                        ng-model="leave.leave_contact" 
                                        class="form-control"
                                        tabindex="17"
                                    ></textarea>
                                </div>

                                <div class="col-md-12" style="margin-bottom: 15px;" ng-show="material.attachment">
                                    <label>เอกสารแนบ :</label>
                                    <div style="display: flex; flex-direction: row; justify-content: flex-start;">
                                        <a  href="{{ url('/'). '/uploads/' }}@{{ material.attachment }}"
                                            title="ไฟล์แนบ"
                                            target="_blank">
                                            <i class="fa fa-paperclip" aria-hidden="true"></i>
                                            @{{ material.attachment }}
                                        </a>

                                        <span style="margin-left: 10px;">
                                            <a href="#">
                                                <span class="glyphicon glyphicon-remove text-danger" aria-hidden="true"></span>
                                            </a>
                                        </span>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>เดือนที่จะดำเนินการ :</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <div class="form-control">
                                            @{{ material.start_month && getMonthName(material.start_month) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>สถานะ :</label>
                                    <div style="border: 1px solid #d2d6de; height: 34px; display: flex; align-items: center; padding: 0 5px;">
                                        <span class="label label-primary" ng-show="material.status == 0">
                                            รอดำเนินการ
                                        </span>
                                        <span class="label label-info" ng-show="material.status == 1">
                                            ดำเนินการแล้วบางส่วน
                                        </span>
                                        <span class="label bg-navy" ng-show="material.status == 2">
                                            ดำเนินการครบแล้ว
                                        </span>
                                        <span class="label label-default" ng-show="material.status == 9">
                                            ยกเลิก
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div style="display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                                    <a
                                        href="#"
                                        ng-click="edit(material.material_id)"
                                        ng-show="!material.approved"
                                        class="btn btn-warning"
                                    >
                                        <i class="fa fa-edit"></i> แก้ไข
                                    </a>
                                    <form
                                        id="frmDelete"
                                        method="POST"
                                        action="{{ url('/material/delete') }}"
                                        ng-show="!material.approved"
                                    >
                                        <input type="hidden" id="id" name="id" value="@{{ material.material_id }}" />
                                        {{ csrf_field() }}
                                        <button
                                            type="submit"
                                            ng-click="delete($event, material.material_id)"
                                            class="btn btn-danger btn-block"
                                        >
                                            <i class="fa fa-trash"></i> ลบ
                                        </button>
                                    </form>
                                </div>
                                <!-- /** Action buttons container */ -->

                            </div>
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->

    </section>

    <script>
        $(function () {
            $('.select2').select2();
        });
    </script>

@endsection