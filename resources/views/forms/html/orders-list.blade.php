<table class="table table-bordered table-striped" style="font-size: 14px; margin: 10px auto;">
    <thead>
        <tr>
            <th style="width: 5%; text-align: center;">#</th>
            <th style="width: 15%; text-align: center;">ใบสั่งซื้อ/จ้าง</th>
            <th style="width: 8%; text-align: center;">ประเภทแผน</th>
            <th>เจ้าหนี้</th>
            <th style="width: 6%; text-align: center;">ปีงบ</th>
            <th style="width: 6%; text-align: center;">จำนวนรายการ</th>
            <th style="width: 8%; text-align: center;">ยอดจัดซื้อ</th>
            <th style="width: 12%; text-align: center;">จทน.พัสดุ</th>
            <th style="width: 10%; text-align: center;">สถานะ</th>
            <th style="width: 8%; text-align: center;">Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr ng-repeat="(index, order) in orders">
            <td style="text-align: center;">@{{ index+pager.from }}</td>
            <td style="text-align: center;">
                @{{ order.order_type.name }}
                เลขที่ @{{ order.po_no }}
                <p style="margin: 0;">วันที่ @{{ order.po_date | thdate }}</p>
            </td>
            <td style="text-align: center;">@{{ order.plan_type.plan_type_name }}</td>
            <td>@{{ order.supplier.supplier_name }}</td>
            <td style="text-align: center;">@{{ order.year }}</td>
            <td style="text-align: center;">
                @{{ order.details.length }}
                <a  href="#"
                    ng-click="showOrderDetails(order.details)"
                    class="btn btn-default btn-xs" 
                    title="รายการ">
                    <i class="fa fa-clone"></i>
                </a>
            </td>
            <td style="text-align: right;">@{{ order.net_total | currency:'':2 }}</td>
            <td style="text-align: center;">
                @{{ order.officer.person_firstname+ ' ' +order.officer.person_lastname }}
            </td>
            <td style="text-align: center;">
                <span class="label label-primary" ng-show="order.status == 0">
                    อยู่ระหว่างจัดซื้อจัดจ้าง
                </span>
                <span class="label label-info" ng-show="order.status == 1">
                    อนุมัติจัดซื้อจัดจ้าง
                </span>
                <span class="label bg-maroon" ng-show="order.status == 2">
                    ตรวจรับแล้วบางงวด
                </span>
                <span class="label label-success" ng-show="order.status == 3">
                    ตรวจรับทั้งหมดแล้ว
                </span>
                <span class="label label-warning" ng-show="order.status == 4">
                    ส่งเบิกเงินแล้ว
                </span>
                <span class="label bg-maroon" ng-show="order.status == 5">
                    อยู่ระหว่างบริหารสัญญา
                </span>
                <span class="label label-danger" ng-show="order.status == 9">
                    ยกเลิก
                </span>
            </td>
            <td style="text-align: center;">
                <a  href="{{ url('/orders/detail') }}/@{{ order.id }}"
                    class="btn btn-primary btn-xs" 
                    title="รายละเอียด">
                    <i class="fa fa-search"></i>
                </a>
                <a  href="{{ url('/orders/edit') }}/@{{ order.id }}"
                    class="btn btn-warning btn-xs"
                    title="แก้ไขรายการ"
                    ng-show="![2,3,4,5,9].includes(order.status)">
                    <i class="fa fa-edit"></i>
                </a>
                <form
                    id="frmDelete"
                    method="POST"
                    action="{{ url('/orders/delete') }}"
                    style="display: inline;"
                    ng-show="![2,3,4,5,9].includes(order.status)"
                >
                    {{ csrf_field() }}
                    <button
                        type="submit"
                        ng-click="delete($event, order.id)"
                        class="btn btn-danger btn-xs"
                    >
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
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
                <a href="#" ng-click="getAllWithUrl($event, pager.path+ '?page=1', setOrders)" aria-label="Previous">
                    <span aria-hidden="true">First</span>
                </a>
            </li>
        
            <li ng-class="{'disabled': (pager.current_page==1)}">
                <a href="#" ng-click="getAllWithUrl($event, pager.prev_page_url, setOrders)" aria-label="Prev">
                    <span aria-hidden="true">Prev</span>
                </a>
            </li>

            <!-- <li ng-repeat="i in debtPages" ng-class="{'active': pager.current_page==i}">
                <a href="#" ng-click="getAllWithUrl($event, pager.path + '?page=' +i, setOrders)">
                    @{{ i }}
                </a>
            </li> -->

            <!-- <li ng-if="pager.current_page < pager.last_page && (pager.last_page - pager.current_page) > 10">
                <a href="#" ng-click="pager.path">
                    ...
                </a>
            </li> -->

            <li ng-class="{'disabled': (pager.current_page==pager.last_page)}">
                <a href="#" ng-click="getAllWithUrl($event, pager.next_page_url, setOrders)" aria-label="Next">
                    <span aria-hidden="true">Next</span>
                </a>
            </li>

            <li ng-if="pager.current_page !== pager.last_page">
                <a href="#" ng-click="getAllWithUrl($event, pager.path+ '?page=' +pager.last_page, setOrders)" aria-label="Previous">
                    <span aria-hidden="true">Last</span>
                </a>
            </li>
        </ul>
    </div>
</div><!-- /.row -->