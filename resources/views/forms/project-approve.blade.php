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
                <table class="layout">
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic">ส่วนราชการ</span>
                                <div class="content__header-text" style="width: 77%; margin-left: 70px;">
                                    <span style="margin: 0 5px;">{{ $project->depart->depart_name }}</span>
                                    <span style="margin: 0 5px;">โรงพยาบาลเทพรัตน์นครราชสีมา</span>
                                    โทร <span style="margin: 0 5px;">{{ thainumDigit($project->depart->tel_no) }}</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="width: 50%;">
                            <div class="content-header">
                                <span class="content__header-topic">ที่</span>
                                <div class="content__header-text" style="width: 90%; margin-left: 12px;">
                                    <span style="margin: 0 5px;">{{ thainumDigit($project->depart->memo_no.'/') }}</span>
                                </div>
                            </div>
                        </td>
                        <td colspan="2">
                            <div class="content-header">
                                <span class="content__header-topic">วันที่</span>
                                <div class="content__header-text" style="width: 70%; margin-left: 28px;">
                                    <span style="margin: 0 10px;">
                                        {{ '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.thainumDigit(convDbDateToLongThMonth(date('Y-m-d'))) }}
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <div class="content-header">
                                <span class="content__header-topic">เรื่อง</span>
                                <div class="content__header-text" style="width: 85%; margin-left: 28px;">
                                    <span style="margin-left: 5px;">ขออนุมัติดำเนินโครงการ</span>
                                </div>
                            </div>
                            <div style="margin: 5px 0; padding: 0;">
                                <span style="font-size: 20px;">เรียน</span>
                                <span style="margin-left: 5px;">ผู้อำนวยการโรงพยาบาลเทพรัตน์นครราชสีมา</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph with-expanded">
                                ตามที่ <span>{{ $project->depart->depart_name }}</span>
                                @if($project->depart->faction_id == '7')
                                    กลุ่มภารกิจด้านพัฒนาระบบบริการฯ
                                @else
                                    <span>{{ $project->depart->faction->faction_name }}</span>
                                @endif
                            </p>
                            <p>
                                ได้รับอนุมัติให้จัดทำโครงการ <span>{{ thainumDigit($project->project_name) }}</span>                                
                                รหัสโครงการ 
                                @if(!empty($project->project_no))
                                    <span style="margin: 0;">{{ thainumDigit($project->project_no) }}</span>
                                @else
                                    <span class="dot">............................</span>
                                @endif
                                งบประมาณสนับสนุนจาก <span>{{ thainumDigit($project->budgetSrc->name) }}</span>โรงพยาบาลฯ
                                ปีงบประมาณ <span>{{ thainumDigit($project->year) }}</span>
                            </p>
                            <p>
                                จำนวน <span>{{ thainumDigit(number_format($project->total_budget)) }}</span> บาท
                                (<span>{{ $project->total_budget_str }}</span>)
                                เป็นโครงการระดับ <span>{{ $project->projectType->name }}</span> นั้น
                                <!-- โดยมีวัตถุประสงค์เพื่อ <span>{{ thainumDigit($project->remark) }}</span> นั้น -->
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph with-expanded" style="padding-right: 5px;">
                                ในการนี้ <span>{{ $project->depart->depart_name}}</span>
                                จึงขออนุมัติดำเนินโครงการ <span>{{ thainumDigit($project->project_name) }}</span> ตามเอกสารที่แนบมาพร้อมนี้
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4">
                            <p class="memo-paragraph">
                                จึงเรียนมาเพื่อโปรดทราบและพิจารณาอนุมัติ
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3" style="text-align: center; padding: 5px;">
                            <div class="signature">
                                <p style="margin: 30px 0 0;">
                                    ( {{ $project->owner->prefix->prefix_name.$project->owner->person_firstname. ' ' .$project->owner->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    <span>{{ $project->owner->position->position_name }}{{ $project->owner->academic ? $project->owner->academic->ac_name : '' }}</span>
                                </p>
                                <p style="margin: 0;">
                                    ผู้รับผิดชอบโครงการ
                                </p>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3" style="text-align: center; padding: 5px;">
                            <div class="signature">
                                <p style="margin: 40px 0 0;">
                                    ( {{ $headOfFaction->prefix->prefix_name.$headOfFaction->person_firstname. ' ' .$headOfFaction->person_lastname }} )
                                </p>
                                <p style="margin: 0;">
                                    <span>{{ $headOfFaction->position->position_name }}{{ $headOfFaction->academic ? $headOfFaction->academic->ac_name : '' }}</span>
                                </p>
                                @if($project->depart->faction_id == '7')
                                    <p style="margin: 0;">
                                        หัวหน้ากลุ่มภารกิจด้านพัฒนาระบบบริการและ
                                    </p>
                                    <p style="margin: 0;">
                                        สนับสนุนบริการสุขภาพ
                                    </p>
                                @else
                                    <p style="margin: 0;">
                                        หัวหน้า{{ $project->depart->faction->faction_name }}
                                    </p>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3" style="text-align: center; padding: 5px;">
                            <p style="margin: 20px 0 20px 0; font-weight: bold;">
                                <span style="margin: 0;">[&nbsp;&nbsp;] อนุมัติ</span>
                                <span style="margin: 20px;">[&nbsp;&nbsp;] ไม่อนุมัติ</span>
                            </p>
                            <div class="signature">
                                <p style="margin: 30px 0 0;">
                                    ( นายชวศักดิ์  กนกกัณฑพงษ์ )
                                </p>
                                <p style="margin: 0;">
                                    ผู้อำนวยการโรงพยาบาลเทพรัตน์นครราชสีมา
                                </p>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>