<div class="modal fade" id="order-details" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="width: 65%;">
        <div class="modal-content">
            <form action="">
                <div class="modal-header">
                    <h5 class="modal-title">รายการพัสดุ</h5>
                    <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button> -->
                </div>
                <div class="modal-body" style="padding-bottom: 0;">
                    <table class="table table-bordered table-striped" style="font-size: 14px; margin-bottom: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 3%; text-align: center;">#</th>
                                <!-- <th style="width: 8%; text-align: center;">ปีงบ</th> -->
                                <th>รายการ</th>
                                <th style="width: 10%; text-align: center;">จำนวน</th>
                                <th style="width: 10%; text-align: center;">ราคา</th>
                                <th style="width: 10%; text-align: center;">เป็นเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="(index, detail) in orderDetails">
                                <td style="text-align: center;">@{{ index+1 }}</td>
                                <!-- <td style="text-align: center;">@{{ detail.year }}</td> -->
                                <td>
                                    <p class="item__spec-text">
                                        @{{ detail.plan.depart.depart_name }}
                                        <span ng-show="detail.plan.division">
                                            / @{{ detail.plan.division.ward_name }}
                                        </span>
                                    </p>
                                    <p style="margin: 0;">@{{ detail.item.category.name }}</p>
                                    @{{ detail.plan.plan_no }} - @{{ detail.item.item_name }}
                                </td>
                                <td style="text-align: center;">
                                    <span>@{{ detail.amount | currency:'':1 }}</span>
                                    <span>@{{ detail.unit.name }}</span>
                                </td>
                                <td style="text-align: center;">
                                    @{{ detail.price_per_unit | currency:'':2 }}
                                </td>
                                <td style="text-align: center;">
                                    @{{ detail.sum_price | currency:'':2 }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div><!-- /.modal-body -->
                <div class="modal-footer" style="padding-bottom: 8px;">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        ปิด
                    </button>
                </div><!-- /.modal-footer -->
            </form>
        </div>
    </div>
</div>
