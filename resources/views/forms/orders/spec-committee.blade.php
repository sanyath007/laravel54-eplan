<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>บันทึกขอสนับสนุน</title>
        <link rel="stylesheet" href="{{ asset('/css/pdf.css') }}">
    </head>
    <body>
        <div class="memo-container">
            <div class="memo-header">
                <div class="logo-krut">
                    <img src="{{ asset('/img/krut.jpg') }}" alt="krut" />
                </div>
                <h2>บันทึกข้อความ</h2>
            </div>
            <div class="content">
                <?php $orderType = in_array($support->plan_type_id, [1,2]) ? 'จัดซื้อ' : '' ?>
                <table class="layout">
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic">ส่วนราชการ</span>
                                <div class="content__header-text" style="width: 87%;">
                                    <span>กลุ่มงานพัสดุ</span>
                                    <span>โรงพยาบาลเทพรัตน์นครราชสีมา</span>
                                    <span>โทร {{ thainumDigit('0 4439 5000 ต่อ '.$departOfParcel->tel_no) }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width: 50%;">
                            <div class="content-header">
                                <span class="content__header-topic">ที่</span>
                                <div class="content__header-text" style="width: 95%;">
                                    <span>{{ thainumDigit($departOfParcel->memo_no.'/'.$support->supportOrders[0]->spec_doc_no) }}</span>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="content-header">
                                <span class="content__header-topic">วันที่</span>
                                <div class="content__header-text" style="width: 88%;">
                                    <span style="margin: 0 10px;">
                                        <!-- {{ '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.thainumDigit($support->doc_date) }} -->
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ thainumDigit(convDbDateToLongThDate($support->supportOrders[0]->spec_doc_date)) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic" style="top: 0;">เรื่อง</span>
                                <div class="content__header-text" style="width: 95%;">
                                    <span style="margin-left: 5px;">
                                        ขออนุมัติแต่งตั้งผู้รับผิดชอบจัดทำรายละเอียดคุณลักษณะเฉพาะของพัสดุและราคากลาง
                                    </span>
                                </div>
                            </div>
                            <div style="margin: 5px 0; padding: 0;">
                                <span style="font-size: 20px;">เรียน</span>
                                <span style="margin-left: 5px;">ผู้ว่าราชการจังหวัดนครราชสีมา</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <?php $purchaseMethods = [1 => 'เฉพาะเจาะจง', 2 => 'ประกวดราคาอิเล็กทรอนิกส์ (e-bidding)']; ?>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                <span class="memo-paragraph-topic-inline">๑.ต้นเรื่อง</span>
                                ด้วย กลุ่มงานพัสดุ โรงพยาบาลเทพรัตน์นครราชสีมา มีความประสงค์จะดำเนินการ
                                {{$orderType}}<span>{{ $support->category->name }}</span>
                                โดยวิธี{{ $purchaseMethods[$support->supportOrders[0]->purchase_method] }} เพื่อใช้ในการปฏิบัติงานของเจ้าหน้าที่
                                จึงขออนุมัติ{{$orderType}}<span>{{ $support->category->name }}</span>
                                จำนวน <span>{{ thainumDigit(count($support->details)) }}</span> รายการ
                                จำนวนเงินทั้งสิ้น <span>{{ thainumDigit(number_format($support->total, 2)) }} บาท</span>
                                <span>({{ baht_text($support->total) }})</span>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph-topic">
                                ๒. ข้อกฎหมาย
                            </p>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                ๒.๑ ระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐
                                ข้อ ๒๑ ในการจัดซื้อจัดจ้างที่มิใช่การจ้างก่อสร้าง ให้หัวหน้าหน่วยงานของรัฐแต่งตั้งคณะกรรมการขึ้นมา คณะหนึ่ง
                                หรือจะให้เจ้าหน้าที่ หรือบุคคลใดบุคคลหนึ่งรับผิดชอบในการจัดทำร่างขอบเขตของงาน หรือราย
                                ละเอียดคุณลักษณะเฉพาะของพัสดุที่จะซื้อหรือจ้าง รวมทั้งกำหนดหลักเกณฑ์พิจารณาคัดเลือกข้อเสนอด้วย และ
                                ข้อ ๒๑ วรรคสี่ องค์ประกอบ ระยะเวลาการพิจารณา และประชุมของคณะกรรมการตามวรรคหนึ่ง
                                และวรรคสอง ให้เป็นไปตามที่หัวหน้าหน่วยงานของรัฐกำหนดตามความจำเป็นและเหมาะสม
                            </p>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                ๒.๒ คำสั่งจังหวัดนครราชสีมา ที่ <span>{{ thainumDigit($provinceOrders[0]->order_no) }}</span>
                                ลงวันที่&nbsp;&nbsp;&nbsp;&nbsp;<span>{{ thainumDigit(convDbDateToLongThDate($provinceOrders[0]->order_date)) }}</span>
                                เรื่อง การมอบอำนวจของผู้ว่าราชการจังหวัดนครราชสีมา ให้ผู้อำนวยการโรงพยาบาลทั่วไปโดยให้มีอำนาจภายในวงเงิน ครั้งหนึ่งไม่เกิน ๑๐,๐๐๐,๐๐๐ บาท
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="memo-paragraph-content with-compressed with-expanded">
                                <span class="memo-paragraph-topic-inline">๓.ข้อพิจารณา</span>
                                ในการนี้ โรงพยาบาลเทพรัตน์นครราชสีมา พิจารณาแล้ว จึงขออนุมัติแต่งตั้ง

                                <?php $committeeType = count($committees) > 1 ? 'คณะ' : ''; ?>
                                <div class="committees-paragraph">
                                    <table style="width: 100%;">
                                        <?php $index = 0;?>
                                        @foreach($committees as $committee)
                                        <tr>
                                            <td style="width: 40%;">
                                                {{ thainumDigit(++$index) }}. {{ $committee->person->prefix->prefix_name.$committee->person->person_firstname.' '.$committee->person->person_lastname }}
                                            </td>
                                            <td>ตำแหน่ง {{ $committee->person->position->position_name }}{{ $committee->person->academic ? $committee->person->academic->ac_name : '' }}</td>
                                        </tr>
                                        @endforeach
                                    </table>
                                </div>

                                เป็น{{ $committeeType }}ผู้รับผิดชอบจัดทำรายละเอียดคุณลักษณะเฉพาะ และราคากลางการ{{$orderType}}<span>{{ $support->category->name }}</span>
                                จำนวน <span>{{ thainumDigit(count($support->details)) }} รายการ</span>
                                เพื่อใช้ในการปฏิบัติงานของเจ้าหน้าที่ โดยมีหน้าที่จัดทำรายละเอียดคุณลักษณะเฉพาะของพัสดุ รวมทั้งกำหนด
                                หลักเกณฑ์พิจารณาคัดเลือกข้อเสนอด้วย</span>
                            </div>
                        </td>
                    </tr>

                    @if(count($committees) > 1)
                        <tr>
                            <td colspan="4">
                                <div style="height: 80px;"></div>
                                <p class="next-paragraph">/๔.ข้อเสนอ...</p>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="4">
                                <div class="memo-paragraph-content with-compressed with-expanded">
                                    <span class="memo-paragraph-topic-inline">๔.ข้อเสนอ</span>
                                    จึงเรียนมาเพื่อโปรดพิจารณา หากเห็นชอบแล้วขอได้โปรดอนุมัติแต่งตั้งผู้จัดทำ
                                    รายละเอียดคุณลักษณะเฉพาะและราคากลาง รวมทั้งหลักเกณฑ์การพิจารณาคัดเลือกข้อเสนอตามรายชื่อที่เสนอ
                                    ในข้อ ๓ ต่อไป
                                </div>
                            </td>
                        </tr>
                    @endif

                    @if(count($committees) > 1)
                        <tr>
                            <td colspan="4">
                                <div class="page-number">- ๒ -</div>
                                <div class="memo-paragraph-content with-compressed with-expanded">
                                    <span class="memo-paragraph-topic-inline">๔.ข้อเสนอ</span>
                                    จึงเรียนมาเพื่อโปรดพิจารณา หากเห็นชอบแล้วขอได้โปรดอนุมัติแต่งตั้งผู้จัดทำ
                                    รายละเอียดคุณลักษณะเฉพาะและราคากลาง รวมทั้งหลักเกณฑ์การพิจารณาคัดเลือกข้อเสนอตามรายชื่อที่เสนอ
                                    ในข้อ ๓ ต่อไป
                                </div>
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="2">
                            <div class="signature-approver" style="padding-top: 25px;">
                                <p style="margin: 0; font-weight: bold;">
                                    <span style="margin: 0;">ชอบ/อนุมัติ</span>
                                </p>
                                <p style="margin: 40px 0 0;">
                                    ( นายชวศักดิ์  กนกกัณฑพงษ์ )
                                </p>
                                <p style="margin: 0;">
                                    ผู้อำนวยการโรงพยาบาลเทพรัตน์นครราชสีมา
                                </p>
                                <p style="margin: 0;">
                                    ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัดนครราชสีมา
                                </p>
                            </div>
                        </td>
                        <td colspan="2" style="text-align: center; padding: 0 5px 0 0;">
                            <div class="signature">
                                <p style="margin: 10px 0 0;">
                                    ลงชื่อ<span class="dot">.........................................เจ้าหน้าที่</span>
                                </p>
                                <p style="margin: 0;">
                                    ( {{ $support->officer->prefix->prefix_name.$support->officer->person_firstname. ' ' .$support->officer->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    <span>{{ $support->officer->position->position_name }}{{ $support->officer->academic ? $support->officer->academic->ac_name : '' }}</span>
                                </p>
                            </div>
                            <div class="signature">
                                <p style="margin: 20px 0 0 0px;">
                                    ลงชื่อ<span class="dot">...............................หัวหน้าเจ้าหน้าที่</span>
                                </p>
                                <p style="margin: 0;">
                                    ( {{ $headOfFaction->prefix->prefix_name.$headOfFaction->person_firstname. ' ' .$headOfFaction->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    <span>{{ $headOfFaction->position->position_name }}{{ $headOfFaction->academic ? $headOfFaction->academic->ac_name : '' }}</span>
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="page-break"></div>

        <!-- ==================================== PAGE 2 ==================================== -->
        <div class="memo-container">
            <div class="memo-header">
                <div class="logo-krut">
                    <img src="{{ asset('/img/krut.jpg') }}" alt="krut" />
                </div>
                <h2>บันทึกข้อความ</h2>
            </div>
            <div class="content">
                <table class="layout">
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic">ส่วนราชการ</span>
                                <div class="content__header-text" style="width: 87%;">
                                    <span>กลุ่มงานพัสดุ</span>
                                    <span>โรงพยาบาลเทพรัตน์นครราชสีมา</span>
                                    <span>โทร {{ thainumDigit('0 4439 5000 ต่อ '.$departOfParcel->tel_no) }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width: 50%;">
                            <div class="content-header">
                                <span class="content__header-topic">ที่</span>
                                <div class="content__header-text" style="width: 95%;">
                                    <span>{{ thainumDigit($departOfParcel->memo_no.'/') }}</span>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="content-header">
                                <span class="content__header-topic">วันที่</span>
                                <div class="content__header-text" style="width: 89%;">
                                    <span style="margin: 0 10px;">
                                        <!-- {{ '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.thainumDigit($support->doc_date) }} -->
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ thainumDigit(convDbDateToLongThMonth(date('Y-m-d'))) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic" style="top: 0;">เรื่อง</span>
                                <div class="content__header-text" style="width: 95%;">
                                    <span style="margin-left: 5px;">
                                        รายงานผลการกำหนดรายละเอียดคุณลักษณะเฉพาะของพัสดุและราคากลาง
                                    </span>
                                </div>
                            </div>
                            <div style="margin: 5px 0; padding: 0;">
                                <span style="font-size: 20px;">เรียน</span>
                                <span style="margin-left: 5px;">ผู้ว่าราชการจังหวัดนครราชสีมา</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                <span class="memo-paragraph-topic-inline">๑.เรื่องเดิม</span>
                                ตามบันทึกที่ <span>{{ thainumDigit($departOfParcel->memo_no.'/'.$support->supportOrders[0]->spec_doc_no) }}</span>
                                ลงวันที่&nbsp;&nbsp;<span>{{ thainumDigit(convDbDateToLongThDate($support->supportOrders[0]->spec_doc_date)) }}</span>
                                ได้แต่งตั้ง ข้าพเจ้า ผู้มีนามข้างท้ายเป็น{{ $committeeType }}ผู้กำหนดรายละเอียดคุณลักษณะเฉพาะและราคากลาง{{$orderType}}
                                <span>{{ $support->category->name }}</span> สนับสนุน การทำงานของเจ้าหน้าที่ ให้ทำงานได้อย่างมีประสิทธิภาพยิ่งขึ้น นั้น
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph-topic">
                                ๒. ข้อกฎหมาย/ระเบียบ
                            </p>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                ๒.๑ พระราชบัญญัติการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐ มาตรา ๔ ใน พระราชบัญญัตินี้
                                "ราคากลาง" หมายความว่า ราคาเพื่อใช้เป็นฐานสำหรับเปรียบเทียบราคาที่ผู้ยื่นข้อเสนอได้ยื่น เสนอไว้
                                ซึ่งสามารถจัดซื้อจัดจ้างได้จริงตามลำดับ (๔) โดยใช้เกณฑ์ราคากลางและคุณลักษณะพื้นฐานการจัดหา
                            </p>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                ๒.๒ ระเบียบกระทรวงการคลังว่าด้วยการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐
                                ข้อ ๒๑ ในการจัดซื้อหรือจัดจ้าง ที่มิใช่การจ้างก่อสร้าง ให้หัวหน้าหน่วยงานของรัฐแต่งตั้งคณะกรรมการขึ้นมา คณะหนึ่ง
                                หรือจะให้เจ้าหน้าที่ หรือบุคคลใดบุคคลหนึ่งรับผิดชอบในการจัดทำร่างขอบเขตของงานหรือราย-
                                ละเอียดคุณลักษณะเฉพาะของพัสดุที่จะซื้อหรือจ้าง รวมทั้งกำหนดหลักเกณฑ์พิจารณาคัดเลือกข้อเสนอด้วย
                            </p>
                            <p class="memo-paragraph-content with-compressed with-expanded">
                                ๒.๓ คำสั่งจังหวัดนครราชสีมา ที่ <span>{{ thainumDigit($provinceOrders[0]->order_no) }}</span>
                                ลงวันที่&nbsp;&nbsp;&nbsp;&nbsp;<span>{{ thainumDigit(convDbDateToLongThDate($provinceOrders[0]->order_date)) }}</span>
                                เรื่อง การแก้ไขการมอบอำนาจของผู้ว่าราชการจังหวัดให้รองผู้ว่าราชการจังหวัด ปลัดจังหวัด หัวหน้าส่วนราชการ
                                ประจำจังหวัดและนายอำเภอ ปฏิบัติราขการแทน และคำสั่งจังหวัดนครราชสีมา ที่ <span>{{ thainumDigit($provinceOrders[1]->order_no) }}</span>
                                ลงวันที่&nbsp;&nbsp;&nbsp;&nbsp;<span>{{ thainumDigit(convDbDateToLongThDate($provinceOrders[1]->order_date)) }}</span>
                                เรื่อง การมอบอำนวจการจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <?php $spacing = strlen(baht_text($support->total)) > 80 ? '&nbsp;' : ''; ?>
                            <?php $unspacing = strlen(baht_text($support->total)) > 80 ? '' : '&nbsp;'; ?>
                            <?php $sourcePrices = [1 => 'ราคาที่ได้จากการจัดซื้อภายใน 2 ปีงบประมาณ', 2 => 'อื่นๆ']; ?>
                            <div class="memo-paragraph-content with-compressed with-expanded">
                                <span class="memo-paragraph-topic-inline">๓.ข้อพิจารณา</span>
                                บัดนี้ ผู้กำหนดรายละเอียดคุณลักษณะ ได้กำหนดรายละเอียดคุณลักษณะ เฉพาะและราคากลาง{{$orderType}}<span>{{ $support->category->name }}</span>
                                จำนวน <span>{{ thainumDigit(count($support->details)) }} รายการ</span>
                                เป็นเงินทั้งสิ้น <span>{{ thainumDigit(number_format($support->total, 2)) }} บาท</span>
                                <span>({{ baht_text($support->total) }})</span>
                                โรงพยาบาลเทพรัตน์นครราชสีมา โดยถือปฏิบัติตาม{{ $spacing }}ระเบียบกระทรวงการคลัง{{ $unspacing }}ว่าด้วยการจัดซื้อจัดจ้าง และการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐
                                ข้อ ๒๑ และได้พิจารณาราคากลาง โดยถือปฏิบัติตามพระราชบัญญัติ การจัดซื้อจัดจ้างและการบริหารพัสดุภาครัฐ พ.ศ. ๒๕๖๐ มาตรา ๔
                                <table style="width: 100%; margin: 10px;" class="table" border="1">
                                    <tr>
                                        <th style="text-align: center;">ลำดับ</th>
                                        <th style="text-align: center;">แหล่งที่มาของราคาอ้างอิง</th>
                                        <th style="text-align: center;">ราคารวม (บาท)</th>
                                    </tr>
                                    <tr>
                                        <td style="text-align: center;">๑</td>
                                        <td style="padding: 0 5px 2px;">
                                            {{ thainumDigit($sourcePrices[$support->supportOrders[0]->source_price]) }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ thainumDigit(number_format($support->total, 2)) }}
                                        </td>
                                    </tr>
                                </table>
                                <p>
                                    ข้าพเจ้าฯ ได้สรุปรายละเอียดคุณลักษณะเฉพาะและราคากลาง{{$orderType}}<span>{{ $support->category->name }}</span>
                                    จำนวน <span>{{ thainumDigit(count($support->details)) }} รายการ</span>
                                    เป็นเงินทั้งสิ้น <span>{{ thainumDigit(number_format($support->total, 2)) }} บาท</span>
                                    <span>({{ baht_text($support->total) }})</span>
                                </p>
                            </div>

                            <p class="next-paragraph">/๔. ข้อเสนอ...</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="page-number">- ๒ -</div>
                            <div class="memo-paragraph-content with-compressed with-expanded" style="margin-bottom: 10px;">
                                <span class="memo-paragraph-topic-inline">๔.ข้อเสนอ</span>
                                จึงเรียนมาเพื่อโปรดพิจารณา ให้ความเห็นชอบรายละเอียดคุณลักษณะเฉพาะ และราคากลาง{{$orderType}}<span>{{ $support->category->name }}</span>
                                จำนวน <span>{{ thainumDigit(count($support->details)) }} รายการ</span>
                                เป็นเงินทั้งสิ้น <span>{{ thainumDigit(number_format($support->total, 2)) }} บาท</span>
                                <span>({{ baht_text($support->total) }})</span> ของโรงพยาบาลเทพรัตน์นครราชสีมา

                                @foreach($committees as $committee)
                                    <div class="clearfix" style="margin-left: 80px;">
                                        <p style="margin: 20px 0 0;">
                                            ลงชื่อ<span class="dot">.........................................ผู้กำหนดรายละเอียดคุณลักษณะ</span>
                                        </p>
                                        <div style="width: 60%; text-align: center;">
                                            <p style="margin: 0">
                                                ( {{ $committee->person->prefix->prefix_name.$committee->person->person_firstname.' '.$committee->person->person_lastname }} )
                                            </p>
                                            <p style="margin: 0">
                                                <span>{{ $committee->person->position->position_name }}{{ $committee->person->academic ? $committee->person->academic->ac_name : '' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                @endforeach

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="memo-paragraph-content with-compressed with-expanded">
                                <span>เรียนผู้ว่าราชการจังหวัดนครราชสีมา โรงพยาบาลเทพรัตน์นครราชสีมา ได้พิจารณาแล้ว
                                ผู้กำหนดรายละเอียดคุณลักษณะเฉพาะ ได้ปฏิบัติตามข้อกฏหมาย/ระเบียบ ตามข้อ ๒
                                เห็นควรพิจารณา ให้ความเห็นชอบรายละเอียดคุณลักษณะเฉพาะ ตามที่ผู้กำหนดรายละเอียดคุณลักษณะ
                                เฉพาะและราคากลาง เพื่อใช้ในการดำเนินการจัดซื้อในครั้งนี้</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3" style="text-align: center; padding: 5px;">
                            <div class="signature">
                                <p style="margin: 30px 0 0;">
                                    ลงชื่อ<span class="dot">.........................................เจ้าหน้าที่</span>
                                </p>
                                <p style="margin: 0;">
                                    ( {{ $support->officer->prefix->prefix_name.$support->officer->person_firstname. ' ' .$support->officer->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    เจ้าหน้าที่พัสดุ
                                </p>
                            </div>
                            <div class="signature">
                                <p style="margin: 30px 0 0 50px;">
                                    ลงชื่อ<span class="dot">.........................................หัวหน้าเจ้าหน้าที่</span>
                                </p>
                                <p style="margin: 0;">
                                    ( {{ $headOfFaction->prefix->prefix_name.$headOfFaction->person_firstname. ' ' .$headOfFaction->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    <span>{{ $headOfFaction->position->position_name }}{{ $headOfFaction->academic ? $headOfFaction->academic->ac_name : '' }}</span>
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3">
                            <div class="signature" style="text-align: center;">
                                <p style="margin: 10px 0 20px 0; font-weight: bold;">
                                    <span style="margin: 0;">ชอบ</span>
                                </p>
                                <p style="margin: 30px 0 0;">
                                    ( นายชวศักดิ์  กนกกัณฑพงษ์ )
                                </p>
                                <p style="margin: 0;">
                                    ผู้อำนวยการโรงพยาบาลเทพรัตน์นครราชสีมา
                                </p>
                                <p style="margin: 0;">
                                    ปฏิบัติราชการแทน ผู้ว่าราชการจังหวัดนครราชสีมา
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>