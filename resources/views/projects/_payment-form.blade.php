<div class="modal fade" id="payment-form" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="frmNewPayment">
                <input type="hidden" id="user" name="user" value="{{ Auth::user()->person_id }}" />

                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มเพิ่มรายการเบิกจ่าย</h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div
                            class="col-md-6 form-group"
                            ng-class="{'has-error has-feedback': newPayment.error['received_date']}"
                        >
                            <label for="">วันที่รับเอกสาร</label>
                            <input
                                type="text"
                                id="received_date"
                                name="received_date"
                                ng-model="newPayment.received_date"
                                ng-change="onPlanTypeSelected(newPayment.received_date)"
                                class="form-control"
                            />
                            <span class="help-block" ng-show="newPayment.error['received_date']">
                                @{{ newPayment.error['received_date'] }}
                            </span>
                        </div>
                        <div
                            class="col-md-6 form-group"
                            ng-class="{'has-error has-feedback': newPayment.error['pay_date']}"
                        >
                            <label for="">วันที่เบิกจ่าย</label>
                            <input
                                type="text"
                                id="pay_date"
                                name="pay_date"
                                ng-model="newPayment.pay_date"
                                class="form-control"
                            />
                            <span class="help-block" ng-show="newPayment.error['pay_date']">
                                @{{ newPayment.error['pay_date'] }}
                            </span>
                        </div>
                        <div
                            class="col-md-6 form-group"
                            ng-class="{'has-error has-feedback': newPayment.error['net_total']}"
                        >
                            <label for="">ยอดเบิกจ่าย</label>
                            <input
                                type="text"
                                id="net_total"
                                name="net_total"
                                ng-model="newPayment.net_total"
                                class="form-control"
                            />
                            <span class="help-block" ng-show="newPayment.error['net_total']">
                                @{{ newPayment.error['net_total'] }}
                            </span>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="">มี AAR</label>
                            <div style="display: flex; gap: 30px;">
                                <div>
                                    <input type="radio" ng-model="newPayment.have_aar" ng-value="0" /> ไม่มี 
                                </div>
                                <div>
                                    <input type="radio" ng-model="newPayment.have_aar" ng-value="1" /> มี
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="">หมายเหตุ</label>
                            <textarea
                                rows=""
                                id="remark"
                                name="remark"
                                ng-model="newPayment.remark"
                                class="form-control"
                            ></textarea>
                        </div>
                    </div>
                </div><!-- /.modal-body -->
                <div class="modal-footer" style="padding-bottom: 8px;">
                    <button
                        ng-click="createNewPayment($event, project.id)"
                        class="btn btn-primary"
                    >
                        บันทึก
                    </button>
                    <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                        ปิด
                    </button>
                </div><!-- /.modal-footer -->
            </form>
        </div>
    </div>
</div>
