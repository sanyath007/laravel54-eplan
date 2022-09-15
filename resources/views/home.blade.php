@extends('layouts.main')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            Dashboard
            <!-- <small>Control panel</small> -->
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content" ng-controller="homeCtrl">
        <!-- /** Filtering Tools */ -->
        <div class="row">
            <div class="col-md-12">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <label for="">ประจำปี :</label>
                    <input
                        type="text"
                        id="dtpYear"
                        name="dtpYear"
                        ng-model="dtpYear"
                        class="form-control"
                        style="width: 180px; margin-left: 5px;"
                    />

                    <div class="btn-group btn-group-toggle" data-toggle="buttons" style="margin-left: 10px;">
                        <label class="btn btn-default active" ng-click="onApprovedToggle($event)">
                            <input type="radio" id="all" name="approved" value="" autocomplete="off" checked /> ทั้งหมด
                        </label>
                        <label class="btn btn-default" ng-click="onApprovedToggle($event)">
                            <input type="radio" id="none" name="approved" value="1" autocomplete="off" /> ไม่อนุมัติ
                        </label>
                        </label>
                        <label class="btn btn-default" ng-click="onApprovedToggle($event)">
                            <input type="radio" id="approved" name="approved" value="2" autocomplete="off" /> อนุมัติ
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <!-- /** Filtering Tools */ -->

        @include('dashboard._stat-cards')

        @include('dashboard._stat-cards2')

        <div class="row">
            <section class="col-lg-6 connectedSortable">

                @include('dashboard._assets')

            </section>
            <section class="col-lg-6 connectedSortable">
    
                @include('dashboard._materials')

            </section>
        </div>
        <div class="row">
            <section class="col-lg-6 connectedSortable">

                @include('dashboard._pie-chart')
                
            </section>
            <section class="col-lg-6 connectedSortable">

                @include('dashboard._project-pie-chart')

            </section>
        </div>
        <div class="row">
            <section class="col-lg-12 connectedSortable">

                @include('dashboard._latest-po')

            </section>
        </div>
    </section>

    <script>
        $(function () {
            $('.select2').select2();
        });
    </script>
@endsection