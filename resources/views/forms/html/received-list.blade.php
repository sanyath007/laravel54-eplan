<table class="table table-bordered table-striped" style="font-size: 14px; margin-bottom: 10px;">
    <thead>
        <tr>
            <th style="width: 3%; text-align: center;">#</th>
            <th style="width: 6%; text-align: center;">เลขที่รับ</th>
            <th style="width: 12%; text-align: center;">เอกสาร</th>
            <th>รายการ</th>
            <th style="width: 8%; text-align: center;">ยอดเงิน</th>
            <th style="width: 15%; text-align: center;">หน่วยงาน</th>
            <th style="width: 12%; text-align: center;">จนท.พัสดุ</th>
            <th style="width: 10%; text-align: center;">สถานะ</th>
            <th style="width: 6%; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr ng-repeat="(index, support) in receiveds">
            <td style="text-align: center;">@{{ index+receiveds_pager.from }}</td>
            <td style="text-align: center;">@{{ support.received_no }}</td>
            <td>
                <p style="margin: 0;">เลขที่ @{{ support.doc_no }}</p>
                <p style="margin: 0;">วันที่ @{{ support.doc_date | thdate }}</p>
            </td>
            <td>
                <div ng-show="support.is_plan_group">
                    @{{ support.plan_group_desc }}
                    จำนวน <span>@{{ support.details[0].amount | currency:'':0 }}</span>
                    <span>@{{ support.details[0].unit.name }}</span>
                    <a href="#" class="text-danger" ng-show="support.details.length > 1" ng-click="showDetailsList($event, support.details);">
                        <i class="fa fa-tags" aria-hidden="true"></i>
                    </a>
                </div>
                <div ng-show="!support.is_plan_group">
                    <span>@{{ support.details[0].plan.plan_no }} - @{{ support.details[0].plan.plan_item.item.item_name }}</span>
                    <span class="label label-success label-xs" ng-show="support.details[0].plan.in_plan == 'I'">ในแผน</span>
                    <span class="label label-danger label-xs" ng-show="support.details[0].plan.in_plan == 'O'">นอกแผน</span>
                    <p style="margin: 0; font-size: 12px; color: red;">
                        (@{{ support.details[0].desc }}
                        จำนวน <span>@{{ support.details[0].amount | currency:'':0 }}</span>
                        <span>@{{ support.details[0].unit.name }}</span>
                        ราคา @{{ support.details[0].price_per_unit | currency:'':0 }} บาท) 
                        <a href="#" ng-show="support.details.length > 1" ng-click="showDetailsList($event, support.details);">
                            ... ดูเพิ่ม <i class="fa fa-chevron-circle-down" aria-hidden="true"></i>
                        </a>
                    </p>
                </div>
            </td>
            <td style="text-align: right;">
                @{{ support.total | currency:'':2 }}
            </td>
            <td style="text-align: center;">
                <p style="margin: 0;">@{{ support.depart.depart_name }}</p>
                <p style="margin: 0;">@{{ support.division.ward_name }}</p>
            </td>
            <td style="text-align: center;">
                @{{ support.officer.person_firstname+ ' ' +support.officer.person_lastname  }}
            </td>
            <td style="text-align: center;">
                <span class="label label-primary" ng-show="support.status == 0">
                    รอดำเนินการ
                </span>
                <span class="label label-info" ng-show="support.status == 1">
                    ส่งเอกสารแล้ว
                </span>
                <span class="label bg-navy" ng-show="support.status == 2">
                    รับเอกสารแล้ว
                </span>
                <span class="label label-default" ng-show="support.status == 9">
                    ยกเลิก
                </span>
                <p class="item__spec-text" style="margin-top: 3px;" ng-show="support.status == 2">
                    (<i class="fa fa-clock-o" aria-hidden="true"></i> @{{ support.received_date | thdate }})
                </p>
            </td>
            <td style="text-align: center;">
                <div style="display: flex; justify-content: center; gap: 2px;">
                    <a
                        href="#"
                        ng-click="cancel($event, support.id)"
                        class="btn btn-danger btn-xs"
                    >
                        ยกเลิก
                    </a>
                    <a
                        href="{{ url('orders/add?support=') }}@{{ support.id }}"
                        class="btn btn-primary btn-xs"
                    >
                        สร้าง PO
                    </a>
                </div>
            </td>             
        </tr>
    </tbody>
</table>

<div class="row">
    <div class="col-md-4">
        หน้า @{{ receiveds_pager.current_page }} จาก @{{ receiveds_pager.last_page }}
    </div>
    <div class="col-md-4" style="text-align: center;">
        จำนวน @{{ receiveds_pager.total }} รายการ
    </div>
    <div class="col-md-4">
        <ul class="pagination pagination-sm no-margin pull-right" ng-show="receiveds_pager.last_page > 1">
            <li ng-if="receiveds_pager.current_page !== 1">
                <a href="#" ng-click="getReceivedsWithUrl($event, receiveds_pager.path+ '?page=1', 2, setReceiveds)" aria-label="Previous">
                    <span aria-hidden="true">First</span>
                </a>
            </li>
        
            <li ng-class="{'disabled': (receiveds_pager.current_page==1)}">
                <a href="#" ng-click="getReceivedsWithUrl($event, receiveds_pager.prev_page_url, 2, setReceiveds)" aria-label="Prev">
                    <span aria-hidden="true">Prev</span>
                </a>
            </li>

            <!-- <li ng-repeat="i in debtPages" ng-class="{'active': receiveds_pager.current_page==i}">
                <a href="#" ng-click="getReceivedsWithUrl(receiveds_pager.path + '?page=' +i, 2, setReceiveds)">
                    @{{ i }}
                </a>
            </li> -->

            <!-- <li ng-if="receiveds_pager.current_page < receiveds_pager.last_page && (receiveds_pager.last_page - receiveds_pager.current_page) > 10">
                <a href="#" ng-click="receiveds_pager.path">
                    ...
                </a>
            </li> -->

            <li ng-class="{'disabled': (receiveds_pager.current_page==receiveds_pager.last_page)}">
                <a href="#" ng-click="getReceivedsWithUrl($event, receiveds_pager.next_page_url, 2, setReceiveds)" aria-label="Next">
                    <span aria-hidden="true">Next</span>
                </a>
            </li>

            <li ng-if="receiveds_pager.current_page !== receiveds_pager.last_page">
                <a href="#" ng-click="getReceivedsWithUrl($event, receiveds_pager.path+ '?page=' +receiveds_pager.last_page, 2, setReceiveds)" aria-label="Previous">
                    <span aria-hidden="true">Last</span>
                </a>
            </li>
        </ul>
    </div>
</div><!-- /.row -->