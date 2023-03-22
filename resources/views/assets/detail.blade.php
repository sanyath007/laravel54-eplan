@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            รายละเอียดแผนครุภัณฑ์
            <!-- <small>preview of simple tables</small> -->
        </h1>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">หน้าหลัก</a></li>
            <li class="breadcrumb-item active">รายละเอียดแผนครุภัณฑ์</li>
        </ol>
    </section>

    <!-- Main content -->
    <section
        class="content"
        ng-controller="planAssetCtrl"
        ng-init="
            initForms({
                departs: {{ $departs }},
                divisions: {{ $divisions }},
                categories: {{ $categories }},
                groups: {{ $groups }}
            }, 1);
            getById({{ $plan->id }}, setEditControls);
        "
    >
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header">
                        <h3 class="box-title">
                            รายละเอียดแผนครุภัณฑ์
                            <span ng-show="{{ $plan->plan_no }}"> : เลขที่ ({{ $plan->plan_no }})</span>
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group col-md-6">
                                    <label>ในแผน/นอกแผน :</label>
                                    <div class="form-control">
                                        <div ng-show="asset.in_plan == 'I'">
                                            <i class="fa fa-check-square-o text-success" aria-hidden="true"></i>
                                            ในแผน 
                                        </div>
                                        <div ng-show="asset.in_plan == 'O'">
                                            <i class="fa fa-check-square-o text-success" aria-hidden="true"></i>
                                            นอกแผน
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>ปีงบ :</label>
                                    <input type="text"
                                            id="year" 
                                            name="year"
                                            ng-model="asset.year"
                                            class="form-control">
                                    </inp>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>กลุ่มภารกิจ :</label>
                                    <select id="faction_id" 
                                            name="faction_id"
                                            ng-model="asset.faction_id" 
                                            class="form-control"
                                            ng-change="onFactionSelected(asset.faction_id)">
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
                                            ng-model="asset.depart_id" 
                                            class="form-control select2"
                                            ng-change="onDepartSelected(asset.depart_id)">
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
                                            ng-model="asset.division_id" 
                                            class="form-control select2">
                                        <option value="">-- เลือกงาน --</option>
                                        <option ng-repeat="division in forms.divisions" value="@{{ division.ward_id }}">
                                            @{{ division.ward_name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>รายการ :</label>
                                    <div class="form-control">@{{ asset.desc }}</div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>รายละเอียด (Spec.) :</label>
                                    <div class="form-control">@{{ asset.spec }}</div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>ราคาต่อหน่วย :</label>
                                    <div class="form-control">@{{ asset.price_per_unit | currency:'':2 }}</div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>จำนวนที่ขอ :</label>
                                    <div style="display: flex; gap: 5px;">
                                        <div class="form-control">@{{ asset.amount | currency:'':0 }}</div>
                                        <div class="form-control">@{{ asset.unit.name }}</div>
                                    </div>
                                </div>

                                <div
                                    class="form-group col-md-4"
                                    ng-class="{'has-error has-feedback': checkValidate(asset, 'sum_price')}"
                                >
                                    <label>รวมเป็นเงิน :</label>
                                    <div class="form-control">@{{ asset.sum_price | currency:'':2 }}</div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label>สาเหตุที่ขอ :</label>
                                    <div class="form-control checkbox-groups">
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="N"
                                                    ng-model="asset.request_cause"> ขอใหม่
                                        </div>
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="R"
                                                    ng-model="asset.request_cause"> ทดแทน
                                        </div>
                                        <div class="checkbox-container">
                                            <input  type="radio"
                                                    id="request_cause"
                                                    name="request_cause"
                                                    value="E"
                                                    ng-model="asset.request_cause"> ขยายงาน
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>จำนวนเดิมที่มี :</label>
                                    <input  type="text"
                                            id="have_amount"
                                            name="have_amount"
                                            ng-model="asset.have_amount"
                                            class="form-control pull-right"
                                            tabindex="8"
                                            ng-change="calculateSumPrice()" />
                                </div>

                                <div class="form-group col-md-4">
                                    <label>แหล่งเงินงบประมาณ :</label>
                                    <select
                                        id="budget_src_id"
                                        name="budget_src_id"
                                        ng-model="asset.budget_src_id"
                                        class="form-control"
                                        tabindex="1"
                                    >
                                        <option value="">-- เลือกแหล่งเงินงบประมาณ --</option>
                                        @foreach($budgetSources as $budgetSource)
                                            <option value="{{ $budgetSource->id }}">{{ $budgetSource->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>ยุทธศาสตร์ :</label>
                                    <select id="strategic_id" 
                                            name="strategic_id"
                                            ng-model="asset.strategic_id"
                                            ng-change="onStrategicSelected(asset.strategic_id);"
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

                                <div class="form-group col-md-6">
                                    <label>Service Plan :</label>
                                    <select id="service_plan_id" 
                                            name="service_plan_id"
                                            ng-model="asset.service_plan_id"
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
                                        id="reason"
                                        name="reason"
                                        rows="4"
                                        ng-model="asset.reason"
                                        class="form-control"
                                    ></textarea>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>หมายเหตุ :</label>
                                    <textarea
                                        id="remark"
                                        name="remark"
                                        rows="4"
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
                                    <label>เดือนที่จะดำเนินการ :</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <div class="form-control">
                                            @{{ asset.start_month && getMonthName(asset.start_month) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label>สถานะ :</label>
                                    <div class="form-control">
                                        <h4 class="label label-primary" ng-show="asset.status == 0">
                                            รอดำเนินการ
                                        </h4>
                                        <h4 class="label label-info" ng-show="asset.status == 1">
                                            ดำเนินการแล้วบางส่วน
                                        </h4>
                                        <h4 class="label bg-navy" ng-show="asset.status == 2">
                                            ดำเนินการครบแล้ว
                                        </h4>
                                        <h4 class="label label-default" ng-show="asset.status == 9">
                                            ยกเลิก
                                        </h4>
                                    </div>
                                </div>

                                <!-- ======================= รายละเอียดการปรับแผน ======================= -->
                                <div class="col-md-12" ng-show="asset.is_adjust" style="padding: 10px; background-color: #EFEFEF;">
                                    @include('shared._adjust-list')
                                </div>
                            </div>

                            <!-- ======================= Action buttons ======================= -->
                            <div class="col-md-2">
                                <div style="display: flex; flex-direction: column; justify-content: center; gap: 0.5rem;">
                                    <a
                                        href="#"
                                        ng-click="edit(asset.id)"
                                        ng-show="!asset.approved"
                                        class="btn btn-warning"
                                    >
                                        <i class="fa fa-edit"></i> แก้ไข
                                    </a>
                                    <a
                                        href="#"
                                        ng-click="onShowChangeForm($event, asset)"
                                        ng-show="!asset.approved && {{ Auth::user()->memberOf->depart_id }} == '4'"
                                        class="btn btn-primary"
                                    >
                                        <i class="fa fa-refresh"></i> เปลี่ยนหมวด
                                    </a>
                                    <form
                                        id="frmDelete"
                                        method="POST"
                                        action="{{ url('/asset/delete') }}"
                                        ng-show="!asset.approved"
                                    >
                                        <input type="hidden" id="id" name="id" value="@{{ asset.id }}" />
                                        {{ csrf_field() }}
                                        <button
                                            type="submit"
                                            ng-click="delete($event, asset.id)"
                                            class="btn btn-danger btn-block"
                                        >
                                            <i class="fa fa-trash"></i> ลบ
                                        </button>
                                    </form>
                                    <div class="btn-group" style="display: flex;" ng-show="{{ Auth::user()->memberOf->depart_id }} == '4'">
                                        <button type="button" class="btn btn-primary" style="width: 100%;">
                                            <i class="fa fa-random"></i>
                                            เปลี่ยนสถานะ
                                        </button>
                                        <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li ng-hide="asset.status == 0">
                                                <a href="#" ng-click="setStatus($event, asset.id, '0')">
                                                    รอดำเนินการ
                                                </a>
                                            </li>
                                            <li ng-hide="asset.status == 1">
                                                <a href="#" ng-click="setStatus($event, asset.id, '1')">
                                                    ดำเนินการแล้วบางส่วน
                                                </a>
                                            </li>
                                            <li ng-hide="asset.status == 2">
                                                <a href="#" ng-click="setStatus($event, asset.id, '2')">
                                                    ดำเนินการครบแล้ว
                                                </a>
                                            </li>
                                            <li ng-hide="asset.status == 9">
                                                <a href="#" ng-click="setStatus($event, asset.id, '9')">
                                                    อยู่ระหว่างการจัดซื้อ
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    <button
                                        type="button"
                                        ng-click="showAdjustForm($event, asset)"
                                        ng-show="(asset.approved && (asset.status == 0 || asset.status == 1)) && {{ Auth::user()->memberOf->depart_id }} == '4'"
                                        class="btn bg-maroon"
                                    >
                                        <i class="fa fa-sliders"></i> ปรับแผน (6 เดือนหลัง)
                                    </button>
                                </div>
                                <!-- ======================= Action buttons ======================= -->

                            </div>
                        </div><!-- /.row -->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->

            </div><!-- /.col -->
        </div><!-- /.row -->

        @include('shared._change-form')
        @include('shared._adjust-form')

    </section>

    <script>
        $(function () {
            $('.select2').select2();

            $('#unit_id').select2({ theme: 'bootstrap' });

            $('#price_per_unit').inputmask("currency", { "placeholder": "0" });

            $('#amount').inputmask("currency",{ "placeholder": "0", digits: 0 });

            $('#sum_price').inputmask("currency", { "placeholder": "0" });
        });
    </script>

@endsection