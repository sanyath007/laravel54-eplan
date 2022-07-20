<div class="modal fade" id="transferForm" tabindex="-1" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="">
                <div class="modal-header">
                    <h5 class="modal-title">ฟอร์มโอน/ย้าย</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding-bottom: 0;">
                    <div class="row">
                        <div class="form-group col-md-12">
                            <label for="">ประเภท</label>
                            <select
                                class="form-control mr-2"
                                ng-model="nurseTransfer.in_out"
                            >
                                <option value="">-- ประเภท --</option>
                                <option value="O">ย้ายออก</option>
                                <option value="I">รับย้าย</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">เลขที่คำสั่ง</label>
                            <input
                                type="text"
                                id="transfer_doc_no"
                                ng-model="nurseTransfer.transfer_doc_no"
                                class="form-control mr-2"
                                placeholder="ระบุเลขที่คำสั่ง..."
                            />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">วันที่คำสั่ง</label>
                            <input
                                type="text"
                                id="transfer_doc_date"
                                class="form-control mr-2"
                                ng-model="nurseTransfer.transfer_doc_date"
                            />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">วันที่โอน/ย้าย</label>
                            <input
                                type="text"
                                id="transfer_date"
                                class="form-control mr-2"
                                ng-model="nurseTransfer.transfer_date"
                            />
                        </div>
                        <div class="form-group col-md-6">
                            <label for="">โอน/ย้ายไป</label>
                            <input
                                type="text"
                                id="transfer_to"
                                ng-model="nurseTransfer.transfer_to"
                                class="form-control mr-2"
                                placeholder="ระบุหน่วยงานที่โอน/ย้ายไป..."
                            />
                        </div>
                        <div class="form-group col-md-12">
                            <label for="">เหตุผลการโอน/ย้าย</label>
                            <textarea
                                rows="5"
                                id="transfer_reason"
                                class="form-control mr-2"
                                ng-model="nurseTransfer.transfer_reason"
                            ></textarea>
                        </div>
                    </div>
                </div><!-- /.modal-body -->
                <div class="modal-footer" style="padding-bottom: 8px;">
                    <button class="btn btn-primary" ng-click="transfer($event)">โอน/ย้าย</button>
                </div><!-- /.modal-footer -->
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
	$(function() {
		$('#transfer_date').datepicker({
			autoclose: true,
			language: 'th',
			format: 'dd/mm/yyyy',
			thaiyear: true
		}).datepicker('update', new Date());

        $('#transfer_doc_date').datepicker({
			autoclose: true,
			language: 'th',
			format: 'dd/mm/yyyy',
			thaiyear: true
		}).datepicker('update', new Date());
	});
</script>