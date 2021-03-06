<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Support;
use App\Models\SupportDetail;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\PlanType;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Committee;
use App\Models\Person;
use App\Models\Faction;
use App\Models\Depart;
use App\Models\Division;

class SupportController extends Controller
{
    public function formValidate(Request $request)
    {
        $rules = [
            // 'doc_no'            => 'required',
            // 'doc_date'          => 'required',
            'topic'             => 'required',
            'year'              => 'required',
            'plan_type_id'      => 'required',
            'depart_id'         => 'required',
            'total'             => 'required',
            'reason'            => 'required',
            'insp_committee'    => 'required',
            'contact_person'    => 'required',
        ];

        if ($request['total'] > 100000) {
            $rules['spec_committee'] = 'required';
        }

        if ($request['total'] > 500000) {
            $rules['env_committee'] = 'required';
        }

        $messages = [
            'doc_no.required'           => 'กรุณาระบุเลขที่เอกสาร',
            'doc_date.required'         => 'กรุณาเลือกวันที่เอกสาร',
            'topic.required'            => 'กรุณาระบุเรื่องเอกสาร',
            'year.required'             => 'กรุณาเลือกปีงบประมาณ',
            'plan_type_id.required'     => 'กรุณาเลือกประเภทพัสดุ',
            'depart_id.required'        => 'กรุณาเลือกกลุ่มงาน',
            'total.required'            => 'กรุณาเลือกถึงวันที่',
            'reason.required'           => 'กรุณาระบุเหตุผลการขอสนับสนุน',
            'spec_committee.required'   => 'กรุณาเลือกคณะกรรมการกำหนดคุณลักษณะ',
            'insp_committee.required'   => 'กรุณาเลือกคณะกรรมการตรวจรับ',
            'env_committee.required'    => 'กรุณาเลือกคณะกรรมการเปิดซอง/พิจารณาราคา',
            'contact_person.required'   => 'กรุณาระบุผู้ประสานงาน',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messageBag = $validator->getMessageBag();

            return [
                'success' => 0,
                'errors' => $messageBag->toArray(),
            ];
        } else {
            return [
                'success' => 1,
                'errors' => $validator->getMessageBag()->toArray(),
            ];
        }
    }

    public function index()
    {
        return view('supports.list', [
            "planTypes"     => PlanType::all(),
            "factions"      => Faction::all(),
            "departs"       => Depart::all(),
        ]);
    }

    public function timeline()
    {
        return view('supports.timeline');
    }

    public function search(Request $req)
    {
        $matched = [];
        $arrStatus = [];
        $conditions = [];
        $pattern = '/^\<|\>|\&|\-/i';

        $year = $req->get('year');
        $type = $req->get('type');
        $supportType = $req->get('stype');
        $depart = Auth::user()->person_id == '1300200009261' ? $req->get('depart') : Auth::user()->memberOf->depart_id;
        $status = $req->get('status');

        if($status != '') {
            if (preg_match($pattern, $status, $matched) == 1) {
                $arrStatus = explode($matched[0], $status);

                if ($matched[0] != '-' && $matched[0] != '&') {
                    array_push($conditions, ['status', $matched[0], $arrStatus[1]]);
                }
            } else {
                array_push($conditions, ['status', '=', $status]);
            }
        }

        $supports = Support::with('planType','depart','division')
                    ->with('details','details.plan','details.plan.planItem.unit')
                    ->with('details.plan.planItem','details.plan.planItem.item')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plan_type_id', $type);
                    })
                    ->when(!empty($supportType), function($q) use ($supportType) {
                        $q->where('support_type_id', $supportType);
                    })
                    ->when(!empty($depart), function($q) use ($depart) {
                        $q->where('depart_id', $depart);
                    })
                    ->when(count($conditions) > 0, function($q) use ($conditions) {
                        $q->where($conditions);
                    })
                    ->when(count($matched) > 0 && $matched[0] == '-', function($q) use ($arrStatus) {
                        $q->whereBetween('status', $arrStatus);
                    })
                    ->orderBy('received_no', 'DESC')
                    ->paginate(10);

        return [
            "supports" => $supports
        ];
    }

    public function getById($id)
    {
        $support = Support::with('planType','depart','division','contact')
                    ->with('details','details.unit','details.plan','details.plan.depart')
                    ->with('details.plan.planItem.unit','details.plan.planItem','details.plan.planItem.item')
                    ->find($id);

        $committees = Committee::with('type','person','person.prefix')
                        ->with('person.position','person.academic')
                        ->where('support_id', $id)
                        ->get();

        return [
            "support"       => $support,
            "committees"    => $committees,
        ];
    }

    public function getSupportDetails(Request $req)
    {
        $year = $req->get('year');
        $type = $req->get('type');
        $supportType = $req->get('supportType');
        $status = $req->get('status');

        $plans = SupportDetail::join('supports', 'supports.id', '=', 'support_details.support_id')
                    ->with('plan','plan.planItem','plan.planItem.item')
                    ->with('plan.planItem.item.category','support.depart','unit')
                    ->where('support_details.status', '2')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('supports.year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('supports.plan_type_id', $type);
                    })
                    ->when(!empty($supportType), function($q) use ($supportType) {
                        $q->where('supports.support_type_id', $supportType);
                    })
                    ->when(!empty($status), function($q) use ($status) {
                        $q->where('supports.status', $status);
                    })
                    ->paginate(10);

        return [
            "plans" => $plans
        ];
    }

    public function detail($id)
    {
        return view('supports.detail', [
            "support"       => Support::find($id),
            "planTypes"     => PlanType::all(),
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::all(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function create()
    {
        return view('supports.add', [
            "planTypes"     => PlanType::all(),
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::all(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function store(Request $req)
    {
        try {
            $person = Person::where('person_id', $req['user'])->with('memberOf','memberOf.depart')->first();
            $doc_no_prefix = $person->memberOf->depart->memo_no;

            $support = new Support;
            $support->doc_no            = $doc_no_prefix.'/'.$req['doc_no'];

            if (!empty($req['doc_date'])) {
                $support->doc_date          = convThDateToDbDate($req['doc_date']);
            }

            $support->topic             = $req['topic'];
            $support->support_type_id   = 1;
            $support->year              = $req['year'];
            $support->depart_id         = $req['depart_id'];
            $support->division_id       = $req['division_id'];
            $support->plan_type_id      = $req['plan_type_id'];
            $support->total             = $req['total'];
            $support->contact_person    = $req['contact_person'];
            $support->reason            = $req['reason'];
            $support->remark            = $req['remark'];
            $support->status            = 0;
            $support->created_user      = $req['user'];
            $support->updated_user      = $req['user'];
            
            if ($support->save()) {
                $supportId = $support->id;

                foreach($req['details'] as $item) {
                    $detail = new SupportDetail;
                    $detail->support_id     = $supportId;
                    $detail->plan_id        = $item['plan_id'];
                    // $detail->desc           = $item['desc'];
                    $detail->price_per_unit = $item['price_per_unit'];
                    $detail->unit_id        = $item['unit_id'];
                    $detail->amount         = $item['amount'];
                    $detail->sum_price      = $item['sum_price'];
                    $detail->status         = 0;
                    $detail->save();

                    /** TODO: should update plan's status to 99=pending  */
                    // $plan = Plan::find($item['plan_id']);
                    // $plan->status = '99';
                    // $plan->save();

                    /** TODO: should update plan's remain_amount by decrease from req->amount  */
                    $planItem = PlanItem::where('plan_id', $item['plan_id'])->first();
                    // ตรวจสอบว่ารายการตัดยอดตามจำนวน หรือ ตามยอดเงิน
                    if ($planItem->calc_method == 1) {
                        $planItem->remain_amount = (float)$planItem->remain_amount - (float)$item['amount'];
                        $planItem->remain_budget = (float)$planItem->remain_budget - (float)$item['sum_price'];
                    } else {
                        $planItem->remain_budget = (float)$planItem->remain_budget - (float)$item['sum_price'];

                        if ($planItem->remain_budget <= 0) {
                            $planItem->remain_amount = 0;
                        }
                    }

                    $planItem->save();
                }
                
                /** คณะกรรมการกำหนดคุณลักษณะ */
                if (count($req['spec_committee']) > 0) {
                    foreach($req['spec_committee'] as $spec) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 1;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $spec['person_id'];
                        $comm->save();
                    }
                }

                /** คณะกรรมการตรวจรับ */
                if (count($req['insp_committee']) > 0) {
                    foreach($req['insp_committee'] as $insp) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 2;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $insp['person_id'];
                        $comm->save();
                    }
                }

                /** คณะกรรมการเปิดซอง/พิจารณาราคา */
                if (count($req['env_committee']) > 0) {
                    foreach($req['env_committee'] as $env) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 3;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $env['person_id'];
                        $comm->save();
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Insertion successfully',
                    'support'   => $support
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function edit($id)
    {
        return view('supports.edit', [
            "support"       => Support::find($id),
            "planTypes"     => PlanType::all(),
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::all(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function update(Request $req, $id)
    {
        try {
            $person = Person::where('person_id', $req['user'])->with('memberOf','memberOf.depart')->first();
            $doc_no_prefix = $person->memberOf->depart->memo_no;

            $support = Support::find($id);
            $support->doc_no            = $doc_no_prefix.'/'.$req['doc_no'];

            if (!empty($req['doc_date'])) {
                $support->doc_date          = convThDateToDbDate($req['doc_date']);
            }

            $support->topic             = $req['topic'];
            $support->support_type_id   = 1;
            $support->year              = $req['year'];
            $support->depart_id         = $req['depart_id'];
            $support->division_id       = $req['division_id'];
            $support->plan_type_id      = $req['plan_type_id'];
            $support->total             = $req['total'];
            $support->contact_person    = $req['contact_person'];
            $support->reason            = $req['reason'];
            $support->remark            = $req['remark'];
            $support->status            = 0;
            $support->created_user      = $req['user'];
            $support->updated_user      = $req['user'];
            
            if ($support->save()) {
                $supportId = $support->id;

                foreach($req['details'] as $item) {
                    if (!array_key_exists('id', $item)) {
                        $detail = new SupportDetail;
                        $detail->support_id     = $supportId;
                        $detail->plan_id        = $item['plan_id'];
                        // $detail->desc           = $item['desc'];
                        $detail->price_per_unit = $item['price_per_unit'];
                        $detail->unit_id        = $item['unit_id'];
                        $detail->amount         = $item['amount'];
                        $detail->sum_price      = $item['sum_price'];
                        $detail->status         = 0;
                        $detail->save();

                        /** TODO: should update plan's status to 99=pending  */
                        // $plan = Plan::find($item['plan_id']);
                        // $plan->status = '99';
                        // $plan->save();
    
                        /** TODO: should update plan's remain_amount by decrease from req->amount  */
                        $planItem = PlanItem::where('plan_id', $item['plan_id'])->first();
                        // ตรวจสอบว่ารายการตัดยอดตามจำนวน หรือ ตามยอดเงิน
                        if ($planItem->calc_method == 1) {
                            $planItem->remain_amount = (float)$planItem->remain_amount - (float)$item['amount'];
                            $planItem->remain_budget = (float)$planItem->remain_budget - (float)$item['sum_price'];
                        } else {
                            $planItem->remain_budget = (float)$planItem->remain_budget - (float)$item['sum_price'];
    
                            if ($planItem->remain_budget <= 0) {
                                $planItem->remain_amount = 0;
                            }
                        }
    
                        $planItem->save();
                    }
                }
                
                /** คณะกรรมการกำหนดคุณลักษณะ */
                if (count($req['spec_committee']) > 0) {
                    foreach($req['spec_committee'] as $spec) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 1;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $spec['person_id'];
                        $comm->save();
                    }
                }

                /** คณะกรรมการตรวจรับ */
                if (count($req['insp_committee']) > 0) {
                    foreach($req['insp_committee'] as $insp) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 2;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $insp['person_id'];
                        $comm->save();
                    }
                }

                /** คณะกรรมการเปิดซอง/พิจารณาราคา */
                if (count($req['env_committee']) > 0) {
                    foreach($req['env_committee'] as $env) {
                        $comm = new Committee;
                        $comm->support_id           = $supportId;
                        $comm->committee_type_id    = 3;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $env['person_id'];
                        $comm->save();
                    }
                }

                return [
                    'status'    => 1,
                    'message'   => 'Updation successfully',
                    'supports'  => Support::with('planType','depart','division')
                                    ->with('details','details.plan','details.plan.planItem.unit')
                                    ->with('details.plan.planItem','details.plan.planItem.item')
                                    ->where('year', $support->year)
                                    ->where('depart_id', $support->depart_id)
                                    ->where('support_type_id', '1')
                                    ->orderBy('received_no', 'DESC')
                                    ->paginate(10)
                                    ->setPath('search')
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function delete(Request $req, $id)
    {
        try {
            $support = Support::find($id);
            $deleted  = $support;

            if ($support->delete()) {
                $details = SupportDetail::where('support_id', $deleted->id)->get();

                foreach($details as $item) {
                    /** Fetch support_details data */
                    $supportDetail = SupportDetail::find($item->id);

                    /** TODO: Revert plans's status to 0=รอดำเนินการ */
                    // Plan::find($item->plan_id)->update(['status' => 0]);
                    
                    /** TODO: Revert plan_items's remain data */
                    $planItem = PlanItem::where('plan_id', $item->plan_id)->first();
                    // ตรวจสอบว่ารายการตัดยอดตามจำนวน หรือ ตามยอดเงิน
                    if ($planItem->calc_method == 1) {
                        $planItem->remain_amount = (float)$planItem->remain_amount + (float)$supportDetail->amount;
                        $planItem->remain_budget = (float)$planItem->remain_budget + (float)$supportDetail->sum_price;
                    } else {
                        $planItem->remain_budget = (float)$planItem->remain_budget + (float)$item['sum_price'];

                        if ($planItem->remain_amount == 0) {
                            $planItem->remain_amount = 1;
                        }
                    }
                    $planItem->update();

                    /** Delete support_details data */
                    $supportDetail->delete();
                }

                /** TODO: Delete all committee of deleted support data */
                // Committee::where('support_id', $deleted->id)->delete();

                return [
                    'status'    => 1,
                    'message'   => 'Deletion successfully',
                    'supports'  => Support::with('planType','depart','division')
                                    ->with('details','details.plan','details.plan.planItem.unit')
                                    ->with('details.plan.planItem','details.plan.planItem.item')
                                    ->where('year', $deleted->year)
                                    ->where('depart_id', $deleted->depart_id)
                                    ->where('support_type_id', '1')
                                    ->orderBy('received_no', 'DESC')
                                    ->paginate(10)
                                    ->setPath('search')
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function send(Request $req)
    {
        try {
            $support = Support::find($req['id']);
            $support->doc_no    = $req['doc_no'];
            $support->doc_date  = convThDateToDbDate($req['doc_date']);
            $support->sent_date = date('Y-m-d');
            $support->sent_user = Auth::user()->person_id;
            $support->status    = 1;

            if ($support->save()) {
                foreach($req['details'] as $detail) {
                    /** Update support_details's status to 1=ส่งเอกสารแล้ว */
                    SupportDetail::where('support_id', $req['id'])->update(['status' => 1]);
                    
                    /** Update plans's status to 1=ส่งเอกสารแล้ว */
                    Plan::where('id', $detail['plan_id'])->update(['status' => 1]);
                }

                return [
                    'status'    => 1,
                    'message'   => 'Support have been sent!!'
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function printForm($id)
    {
        $support = Support::with('planType','depart','division')
                    ->with('details','details.plan','details.plan.planItem.unit')
                    ->with('details.plan.planItem','details.plan.planItem.item')
                    ->find($id);

        $committees = Committee::with('type','person','person.prefix')
                        ->with('person.position','person.academic')
                        ->where('support_id', $id)
                        ->get();
        
        $contact = Person::where('person_id', $support->contact_person)
                            ->with('prefix','position')
                            ->first();

        $headOfFaction = Person::join('level', 'personal.person_id', '=', 'level.person_id')
                            ->where('level.faction_id', $support->depart->faction_id)
                            ->where('level.duty_id', '1')
                            ->with('prefix','position')
                            ->first();
        
        $headOfDepart = Person::join('level', 'personal.person_id', '=', 'level.person_id')
                            ->where('level.depart_id', $support->depart_id)
                            ->where('level.duty_id', '2')
                            ->with('prefix','position')
                            ->first();

        $data = [
            "support"       => $support,
            "contact"       => $contact,
            "committees"    => $committees,
            "headOfFaction" => $headOfFaction,
            "headOfDepart"  => $headOfDepart,
        ];

        /** Invoke helper function to return view of pdf instead of laravel's view to client */
        return renderPdf('forms.support-form', $data);
    }
}
