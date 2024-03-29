<div class="modal fade" id="spec-form" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายละเอียดคุณลักษณะ</h5>
            </div>
            <div class="modal-body">
                <input
                    type="hidden"
                    id="selectedIndex"
                    name="selectedIndex"
                    ng-model="selectedIndex"
                />

                <div class="row">
                    <div class="col-md-12 form-group">
                        <textarea
                            type="text"
                            id="spec"
                            name="spec"
                            ng-model="order.details[selectedIndex].spec"
                            ng-show="selectedIndex != -1"
                            rows="5"
                            class="form-control"
                        ></textarea>
                        <textarea
                            type="text"
                            id="planGroup_spec"
                            name="planGroup_spec"
                            ng-model="order.details[0].spec"
                            ng-show="selectedIndex == -1"
                            rows="5"
                            class="form-control"
                        ></textarea>
                    </div>
                </div>
            </div><!-- /.modal-body -->
            <div class="modal-footer" style="padding-bottom: 8px;">
                <button
                    ng-click="addSpec($event)"
                    class="btn btn-primary"
                    aria-label="Save"
                >
                    บันทึก
                </button>
                <button class="btn btn-danger" data-dismiss="modal" aria-label="Close">
                    ปิด
                </button>
            </div><!-- /.modal-footer -->
        </div>
    </div>
</div>
