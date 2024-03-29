<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Support;
use App\Models\SupportDetail;
use App\Models\SupportSubitem;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\PlanType;
use App\Models\Item;
use App\Models\SubItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Committee;
use App\Models\Person;
use App\Models\Faction;
use App\Models\Depart;
use App\Models\Division;

class RepairController extends Controller
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
        return view('repairs.list', [
            "factions"      => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function search(Request $req)
    {
        $matched = [];
        $arrStatus = [];
        $conditions = [];
        $pattern = '/^\<|\>|\&|\-/i';

        $year       = $req->get('year');
        $docNo      = $req->get('doc_no');
        $desc       = $req->get('desc');
        $faction    = (Auth::user()->person_id == '1300200009261' || Auth::user()->memberOf->depart_id == '2' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('faction') : Auth::user()->memberOf->faction_id;
        $depart     = (Auth::user()->person_id == '1300200009261' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->depart_id == '2' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('depart') : Auth::user()->memberOf->depart_id;
        $division   = (Auth::user()->person_id == '1300200009261' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->depart_id == '2' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('division') : Auth::user()->memberOf->ward_id;
        $inPlan = $req->get('in_plan');
        $status     = $req->get('status');

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

        $departsList = Depart::when(!empty($faction), function($q) use ($faction) {
                            $q->where('faction_id', $faction);
                        })
                        ->when(!empty($depart), function($q) use ($depart) {
                            $q->where('depart_id', $depart);
                        })
                        ->pluck('depart_id');

        $supportsList = SupportDetail::leftJoin('plan_items','plan_items.plan_id','=','support_details.plan_id')
                        ->join('plans','plans.id','=','plan_items.plan_id')
                        ->where('plans.year', $year)
                        ->where('plan_items.have_subitem', '1')
                        ->when(!empty($desc), function($q) use ($desc) {
                            $q->where('desc', 'like', '%'.$desc.'%');
                        })
                        ->when(!empty($inPlan), function($q) use ($inPlan) {
                            $q->where('plans.in_plan', $inPlan);
                        })
                        ->pluck('support_details.support_id');

        $supports = Support::with('planType','depart','division')
                    ->with('details','details.unit','details.plan','details.plan.planItem.unit')
                    ->with('details.plan.planItem','details.plan.planItem.item')
                    ->where('support_type_id', '2')
                    ->where('plan_type_id', '3')
                    ->whereIn('id', $supportsList)
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
                    })
                    ->when(!empty($faction) || !empty($depart), function($q) use ($departsList) {
                        $q->whereIn('depart_id', $departsList);
                    })
                    ->when(!empty($docNo), function($q) use ($docNo) {
                        $q->where('doc_no', 'like', '%'.$docNo.'%');
                    })
                    ->when(!empty($desc), function($q) use ($supportsList) {
                        $q->whereIn('id', $supportsList);
                    })
                    ->when(count($conditions) > 0, function($q) use ($conditions) {
                        $q->where($conditions);
                    })
                    ->when(count($matched) > 0 && $matched[0] == '-', function($q) use ($arrStatus) {
                        $q->whereBetween('status', $arrStatus);
                    });

        return [
            "sumSupports"   => $supports->sum('total'),
            "supports"      => $supports->paginate(10)
        ];
    }

    public function getAll(Request $req)
    {
        $matched = [];
        $arrStatus = [];
        $conditions = [];
        $pattern = '/^\<|\>|\&|\-/i';

        $year = $req->get('year');
        $type = $req->get('type');
        $supportType = $req->get('stype');
        // $depart = Auth::user()->person_id == '1300200009261' ? $req->get('depart') : Auth::user()->memberOf->depart_id;
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

        $supportsList = SupportDetail::leftJoin('plan_items','plan_items.plan_id','=','support_details.plan_id')
                        ->join('plans','plans.id','=','plan_items.plan_id')
                        ->where('plans.year', $year)
                        ->where('plan_items.have_subitem', '1')
                        ->pluck('support_details.support_id');

        $supports = Support::with('planType','depart','division')
                    ->with('details','details.unit','details.plan','details.plan.planItem.unit')
                    ->with('details.plan.planItem','details.plan.planItem.item')
                    ->where('plan_type_id', '3')
                    ->whereIn('id', $supportsList)
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
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
                    ->paginate(10);

        return [
            "supports" => $supports
        ];
    }

    public function getById($id)
    {
        $support = Support::with('planType','depart','division','contact')
                    ->with('details','details.plan','details.plan.planItem.unit')
                    ->with('details.plan.planItem','details.plan.planItem.item')
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

    public function detail($id)
    {
        return view('repairs.detail', [
            "support"       => Support::find($id),
            "planTypes"     => PlanType::all(),
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function create()
    {
        $year = 2566;
        $depart = Auth::user()->person_id == '1300200009261' ? '' : Auth::user()->memberOf->depart_id;

        $plans = Plan::join('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->with('budget','depart','division')
                    ->with('planItem','planItem.unit')
                    ->with('planItem.item','planItem.item.category')
                    ->where('plans.approved', 'A')
                    ->where('plans.plan_type_id', '3')
                    ->where('plan_items.have_subitem', '1')
                    ->where('plan_items.is_repairing_item', '1')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('plans.year', $year);
                    })
                    ->when(!empty($depart), function($q) use ($depart) {
                        $q->where('plans.depart_id', $depart);
                    })
                    ->where('plan_items.remain_amount', '>', 0)
                    ->get();

        return view('repairs.add', [
            "plans"         => $plans,
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::whereNotIn('faction_id', [6,4,12])->get(),
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

            $support->support_type_id   = 2;
            $support->topic             = $req['topic'];
            $support->year              = $req['year'];
            $support->depart_id         = $req['depart_id'];
            $support->division_id       = $req['division_id'];
            $support->plan_type_id      = $req['plan_type_id'];
            $support->category_id       = '44';
            $support->total             = currencyToNumber($req['total']);
            $support->contact_person    = $req['contact_person'];
            $support->reason            = $req['reason'];
            $support->remark            = $req['remark'];
            $support->status            = 0;
            $support->created_user      = $req['user'];
            $support->updated_user      = $req['user'];
            
            if ($support->save()) {
                foreach($req['details'] as $item) {
                    $detail = new SupportDetail;
                    $detail->support_id     = $support->id;
                    $detail->plan_id        = $req['plan_id'];
                    $detail->desc           = $item['desc'];
                    $detail->price_per_unit = currencyToNumber($item['price_per_unit']);
                    $detail->unit_id        = $item['unit_id'];
                    $detail->amount         = currencyToNumber($item['amount']);
                    $detail->sum_price      = currencyToNumber($item['sum_price']);
                    $detail->status         = 0;
                    $detail->save();
                }
                
                /** คณะกรรมการกำหนดคุณลักษณะ */
                if (count($req['spec_committee']) > 0) {
                    foreach($req['spec_committee'] as $spec) {
                        $comm = new Committee;
                        $comm->support_id           = $support->id;
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
                        $comm->support_id           = $support->id;
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
                        $comm->support_id           = $support->id;
                        $comm->committee_type_id    = 3;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $env['person_id'];
                        $comm->save();
                    }
                }

                return [
                    'status' => 1,
                    'message' => 'Insertion successfully'
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                'status'    => 0,
                'message'   => $ex->getMessage()
            ];
        }
    }

    public function edit(Request $req, $id)
    {
        $year = 2566;
        $depart = Auth::user()->person_id == '1300200009261' ? '' : Auth::user()->memberOf->depart_id;

        $plans = Plan::join('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->with('budget','depart','division')
                    ->with('planItem','planItem.unit')
                    ->with('planItem.item','planItem.item.category')
                    ->where('plans.approved', 'A')
                    ->where('plans.plan_type_id', '3')
                    ->where('plan_items.have_subitem', '1')
                    ->where('plan_items.is_repairing_item', '1')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('plans.year', $year);
                    })
                    ->when(!empty($depart), function($q) use ($depart) {
                        $q->where('plans.depart_id', $depart);
                    })
                    ->where('plan_items.remain_amount', '>', 0)
                    ->get();

        return view('repairs.edit', [
            "repair"        => Support::find($id),
            "plans"         => $plans,
            "categories"    => ItemCategory::all(),
            "units"         => Unit::all(),
            "factions"      => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"       => Depart::all(),
            "divisions"     => Division::all(),
        ]);
    }

    public function update(Request $req, $id)
    {
        try {
            $support = Support::find($id);
            $support->doc_no            = $req['doc_prefix'].'/'.$req['doc_no'];

            if (!empty($req['doc_date'])) {
                $support->doc_date          = convThDateToDbDate($req['doc_date']);
            }

            $support->support_type_id   = 2;
            $support->topic             = $req['topic'];
            $support->year              = $req['year'];
            $support->depart_id         = $req['depart_id'];
            $support->division_id       = $req['division_id'];
            $support->plan_type_id      = $req['plan_type_id'];
            $support->category_id       = '44';
            $support->total             = currencyToNumber($req['total']);
            $support->contact_person    = $req['contact_person'];
            $support->reason            = $req['reason'];
            $support->remark            = $req['remark'];
            $support->status            = 0;
            $support->updated_user      = $req['user'];
            
            if ($support->save()) {
                /** Delete support_detials data that user remove from table list */
                if (count($req['removed']) > 0) {
                    foreach($req['removed'] as $rm) {
                        SupportDetail::where('id', $rm)->delete();
                    }
                }

                foreach($req['details'] as $item) {
                    if (!array_key_exists('id', $item)) {
                        $detail = new SupportDetail;
                        $detail->support_id     = $support->id;
                        $detail->plan_id        = $req['plan_id'];

                        if (!empty($item['subitem_id'])) {
                            $detail->subitem_id     = $item['subitem_id'];
                        }

                        $detail->desc           = $item['desc'];
                        $detail->price_per_unit = currencyToNumber($item['price_per_unit']);
                        $detail->unit_id        = $item['unit_id'];
                        $detail->amount         = currencyToNumber($item['amount']);
                        $detail->sum_price      = currencyToNumber($item['sum_price']);
                        $detail->status         = 0;
                        $detail->save();
                    } else {
                        $detail = SupportDetail::find($item['id']);
                        $detail->support_id     = $support->id;
                        $detail->plan_id        = $req['plan_id'];

                        if (!empty($item['subitem_id'])) {
                            $detail->subitem_id     = $item['subitem_id'];
                        }

                        $detail->desc           = $item['desc'];
                        $detail->price_per_unit = currencyToNumber($item['price_per_unit']);
                        $detail->unit_id        = $item['unit_id'];
                        $detail->amount         = currencyToNumber($item['amount']);
                        $detail->sum_price      = currencyToNumber($item['sum_price']);
                        $detail->save();
                    }
                }

                /** Delete all committees of updated supoorts */
                Committee::where('support_id', $support->id)->delete();
                
                /** คณะกรรมการกำหนดคุณลักษณะ */
                if (count($req['spec_committee']) > 0) {
                    foreach($req['spec_committee'] as $spec) {
                        $comm = new Committee;
                        $comm->support_id           = $support->id;
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
                        $comm->support_id           = $support->id;
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
                        $comm->support_id           = $support->id;
                        $comm->committee_type_id    = 3;
                        $comm->detail               = '';
                        $comm->year                 = $req['year'];
                        $comm->person_id            = $env['person_id'];
                        $comm->save();
                    }
                }

                return [
                    'status' => 1,
                    'message' => 'Updating successfully'
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => 'Something went wrong!!'
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
            $deleted = $support;

            if ($support->delete()) {
                /** Fetch support_details data and update plan's status */
                $details = SupportDetail::where('support_id', $deleted->id)->get();
                foreach($details as $item) {
                    /** TODO: Revert plans's status to 0=รอดำเนินการ or 1=ดำเนินการแล้วบางส่วน */
                    $planItem = PlanItem::where('plan_id', $item->plan_id)->first();
                    if ($planItem->calc_method == 1) {
                        Plan::find($item->plan_id)->update(['status' => 0]);
                    } else {
                        if ($planItem->sum_price == $planItem->remain_budget) {
                            Plan::find($item->plan_id)->update(['status' => 0]);
                        } else {
                            Plan::find($item->plan_id)->update(['status' => 1]);
                        }
                    }
                }

                /** TODO: Delete support_details data */
                SupportDetail::where('support_id', $deleted->id)->delete();

                /** TODO: Delete all committee of deleted support data */
                Committee::where('support_id', $deleted->id)->delete();

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
            $support->sent_date = date('Y-m-d');
            $support->sent_user = Auth::user()->person_id;
            $support->status = 1;

            if ($support->save()) {
                foreach($req['details'] as $detail) {
                    /** Update support_details's status to 1=ส่งเอกสารแล้ว */
                    SupportDetail::where('support_id', $req['id'])->update(['status' => 1]);

                    /** Update plans's status to 9=อยู่ระหว่างการจัดซื้อ */
                    Plan::where('id', $detail['plan_id'])->update(['status' => 9]);
                }

                return [
                    'status'    => 1,
                    'support'   => $support
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status'    => 0,
                'message'   => 'Something went wrong!!'
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
