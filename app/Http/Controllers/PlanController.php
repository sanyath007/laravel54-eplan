<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Validation\Rule;
use Illuminate\Support\MessageBag;
use App\Models\Plan;
use App\Models\PlanItem;
use App\Models\PlanAdjustment;
use App\Models\PlanType;
use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Person;
use App\Models\Faction;
use App\Models\Depart;
use App\Models\Division;
use PDF;

class PlanController extends Controller
{
    public function formValidate (Request $request)
    {
        $rules = [
            'year'              => 'required',
            'plan_no'           => 'required',
            'category_id'       => 'required',
            'desc'              => 'required',
            'price_per_unit'    => 'required',
            'unit_id'           => 'required',
            'amount'            => 'required',
            'sum_price'         => 'required',
            'depart_id'         => 'required',
            // 'division_id'       => 'required',
            'start_month'       => 'required',
            // 'reason'            => 'required',
        ];

        if ($request['leave_type'] == '1' || $request['leave_type'] == '2' || 
            $request['leave_type'] == '3' || $request['leave_type'] == '4' ||
            $request['leave_type'] == '5') {
            $rules['leave_contact'] = 'required';
        }

        $messages = [
            'start_date.required'   => 'กรุณาเลือกจากวันที่',
            'start_date.not_in'     => 'คุณมีการลาในวันที่ระบุแล้ว',
            'end_date.required'     => 'กรุณาเลือกถึงวันที่',
            'end_date.not_in'       => 'คุณมีการลาในวันที่ระบุแล้ว',
        ];

        $validator = \Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $messageBag = $validator->getMessageBag();

            // if (!$messageBag->has('start_date')) {
            //     if ($this->isDateExistsValidation(convThDateToDbDate($request['start_date']), 'start_date') > 0) {
            //         $messageBag->add('start_date', 'คุณมีการลาในวันที่ระบุแล้ว');
            //     }
            // }

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

    public function isExisted($itemId, $year, $depart, $division)
    {
        $planCount = Plan::join('plan_items','plan_items.plan_id','=','plans.id')
                        ->where('plans.year',  $year)
                        ->where('plan_items.item_id',  $itemId)
                        ->when(!empty($depart), function($q) use ($depart) {
                            $q->where('plans.depart_id', $depart);
                        })
                        ->when(!empty($division), function($q) use ($division) {
                            $q->where('plans.division_id', $division);
                        })
                        ->get()
                        ->count();

        return [
            'isExisted' => $planCount > 0
        ];
    }

    public function search(Request $req)
    {
        $plans = $this->getData($req);

        return [
            "plansTotal"    => $plans->get(),
            "plans"         => $plans->paginate(10)
        ];
    }

    public function searchGroups(Request $req, $cate)
    {
        $matched = [];
        $arrStatus = [];
        $conditions = [];
        $pattern = '/^\<|\>|\&|\-/i';

        /** Get params from query string */
        $year   = $req->get('year');
        $faction = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->depart_id == '4') ? $req->get('faction') : Auth::user()->memberOf->faction_id;
        $depart = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->depart_id == '4') ? $req->get('depart') : Auth::user()->memberOf->depart_id;
        $division = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->duty_id == '2' || Auth::user()->memberOf->depart_id == '4') ? $req->get('division') : '';
        $approved = $req->get('approved');
        $inStock = $req->get('in_stock');
        $name = $req->get('name');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plansList = Plan::where('status', 0)
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->when($approved != '', function($q) use ($approved) {
                            $q->where('approved', $approved);
                        })
                        ->when(!empty($depart), function($q) use ($depart, $cate) {
                            if (($depart == '39' && $cate == '3') || ($depart == '65' && $cate == '4')) {
                            
                            } else {
                                $q->where('plans.depart_id', $depart);
                            }
                        })->pluck('id');

        $planGroups = \DB::table('plan_items')
                        ->select(
                            'plan_items.item_id','items.item_name',
                            'items.price_per_unit','items.unit_id',
                            \DB::raw('units.name as unit_name'),
                            \DB::raw('SUM(plan_items.amount) as amount'),
                            \DB::raw('SUM(plan_items.sum_price) as sum_price')
                        )
                        ->leftJoin('items', 'plan_items.item_id', '=', 'items.id')
                        ->leftJoin('units', 'plan_items.unit_id', '=', 'units.id')
                        ->when(!empty($cate), function($q) use ($cate) {
                            $q->where('items.category_id', $cate);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where(function($query) use ($name) {
                                $query->where('items.item_name', 'like', '%'.$name.'%');
                                $query->orWhere('items.en_name', 'like', '%'.$name.'%');
                            });
                        })
                        ->whereIn('plan_items.plan_id', $plansList)
                        ->groupBy('plan_items.item_id')
                        ->groupBy('items.item_name')
                        ->groupBy('items.price_per_unit')
                        ->groupBy('items.unit_id')
                        ->groupBy('units.name')
                        ->orderBy(\DB::raw('SUM(plan_items.amount)'), 'DESC')
                        ->paginate(10);

        $planItemsList = PlanItem::leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                        ->when(!empty($cate), function($q) use ($cate) {
                            $q->where('items.category_id', $cate);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where(function($query) use ($name) {
                                $query->where('items.item_name', 'like', '%'.$name.'%');
                                $query->orWhere('items.en_name', 'like', '%'.$name.'%');
                            });
                        })
                        ->whereIn('plan_items.plan_id', $plansList)
                        ->pluck('plan_items.plan_id');

        $plans = Plan::join('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                        ->with('budget','depart','division')
                        ->with('planItem','planItem.unit')
                        ->with('planItem.item','planItem.item.category')
                        ->where('status', 0)
                        ->when(!empty($year), function($q) use ($year) {
                            $q->where('year', $year);
                        })
                        ->whereIn('plans.id', $planItemsList)
                        // ->when(!empty($faction), function($q) use ($departsList, $depart, $cate) {
                        //     if (($depart != '39' && $cate != '3') && ($depart != '65' && $cate != '4')) {
                        //         $q->whereIn('plans.depart_id', $departsList);
                        //     }
                        // })
                        // ->when(!empty($depart), function($q) use ($depart, $cate) {
                        //     if (($depart != '39' && $cate != '3') && ($depart != '65' && $cate != '4')) {
                        //         $q->where('plans.depart_id', $depart);
                        //     }
                        // })
                        ->get();

        return [
            'plans'         => $plans,
            'planGroups'    => $planGroups,
        ];
    }

    public function getAll()
    {
        return [
            'plans' => Plan::with('budget','depart','division')
                            ->with('planItem','planItem.unit')
                            ->with('planItem.item','planItem.item.category')
                            ->orderBy('plan_no')
                            ->get(),
        ];
    }

    public function getById($id)
    {
        return [
            'plan' => Plan::where('id', $id)
                        ->with('budget','depart','depart.faction','division','strategic','servicePlan')
                        ->with('planItem','planItem.unit','planItem.item','planItem.item.category')
                        ->with('planItem.building','adjustments','adjustments.unit')
                        ->first(),
        ];
    }

    public function getBalance($id)
    {
        return [
            'plan' => PlanItem::where('plan_id', $id)->first(),
        ];
    }

    public function store(Request $req)
    {
        $plan = new Plan();
        // $plan->year      = calcBudgetYear($req['year']);
        $plan->year         = $req['year'];
        $plan->plan_no      = $req['plan_no'];
        $plan->depart_id    = $req['depart_id'];
        $plan->division_id  = $req['division_id'];
        $plan->start_month  = $req['start_month'];
        $plan->reason       = $req['reason'];
        $plan->remark       = $req['remark'];
        $plan->status       = '0';

        /** Upload attach file */
        // $attachment = uploadFile($req->file('attachment'), 'uploads/');
        // if (!empty($attachment)) {
        //     $plan->attachment = $attachment;
        // }

        if($plan->save()) {
            $planId = $plan->id;

            $asset = new PlanAsset();
            $asset->plan_id         = $planId;
            $asset->category_id     = $req['category_id'];
            $asset->desc            = $req['desc'];
            $asset->spec            = $req['spec'];
            $asset->price_per_unit  = $req['price_per_unit'];
            $asset->unit_id         = $req['unit_id'];
            $asset->amount          = $req['amount'];
            $asset->sum_price       = $req['sum_price'];
            $asset->save();

            return redirect('/assets/list');
        }
    }

    public function update(Request $req)
    {
        //
    }

    public function delete(Request $req, $id)
    {
        try {
            $plan = Plan::find($id);
            $deleted = $plan;

            if($plan->delete()) {
                if (PlanItem::where('plan_id', $id)->delete()) {
                    return [
                        'status'    => 1,
                        'message'   => 'Deletion successfully!!',
                        'plans'     => Plan::join('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                                        ->with('budget','depart','division')
                                        ->with('planItem','planItem.unit')
                                        ->with('planItem.item','planItem.item.category')
                                        ->where('plan_type_id', $deleted->plan_type_id)
                                        ->where('year', $deleted->year)
                                        ->where('depart_id', $deleted->depart_id)
                                        ->paginate(10)
                                        ->setPath('search')
                    ];
                }
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

    public function change(Request $req, $id)
    {
        try {
            $plan = Plan::find($id);
            $plan->plan_type_id     = $req['plan_type_id'];
            $plan->updated_user     = $req['user'];
    
            if($plan->save()) {
                $item = Item::find($req['item_id']);
                $item->plan_type_id = $req['plan_type_id'];
                $item->category_id  = $req['category_id'];
                $item->group_id     = $req['group_id'];
                $item->save();
                
                return [
                    'status'    => 1,
                    'message'   => 'Changing successfully!!',
                    'plan'      => $plan
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

    public function setStatus(Request $req, $id)
    {
        try {
            $plan = Plan::find($id);
            $plan->status = $req['status'];
    
            if($plan->save()) {
                return [
                    'status'    => 1,
                    'message'   => 'Setting status successfully!!',
                    'plan'      => $plan
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

    public function cancel(Request $req, $id)
    {
        try {
                 /** Get old data of found plan */
            $oldPlan = PlanItem::with('plan')->where('plan_id', $id)->first();

            /** Update found plan_items table */
            $plan = Plan::find($id);
            $plan->is_adjust    = 1;
            $plan->status       = 99;

            if($plan->save()) {
                /** Create new plan adjustment data */
                $adjustment = new PlanAdjustment;
                $adjustment->plan_id            = $id;
                $adjustment->adjust_type        = '9';
                $adjustment->in_plan            = $oldPlan->plan->in_plan;
                $adjustment->old_price_per_unit = $oldPlan->price_per_unit;
                $adjustment->old_unit_id        = $oldPlan->unit_id;
                $adjustment->old_amount         = $oldPlan->amount;
                $adjustment->old_sum_price      = $oldPlan->sum_price;
                $adjustment->save();

                return [
                    'status'    => 1,
                    'message'   => 'Cancel plan successfully!!',
                    'plan'      => $plan
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

    public function storeAdjust(Request $req, $id)
    {
        try {
            /** Get old data of found plan */
            $oldPlan = PlanItem::with('plan')->where('plan_id', $id)->first();

            /** Update found plan_items table */
            $plan = PlanItem::where('plan_id', $id)->first();
            $plan->price_per_unit   = currencyToNumber($req['price_per_unit']);
            $plan->unit_id          = $req['unit_id'];

            /** คำนวณยอดการใช้ */
            $used_amount = $plan->amount - $plan->remain_amount;
            $used_budget = $plan->sum_price - $plan->remain_budget;

            if($plan->calc_method == 1) {
                /** กรณีตัดยอดตามจำนวน */
                if($plan->sum_price < currencyToNumber($req['sum_price'])) {
                    /** กรณีขยายงบประมาณ */
                    $plan->remain_amount = currencyToNumber($req['amount']) - $plan->remain_amount;
                    $plan->remain_budget = currencyToNumber($req['sum_price']) - $plan->remain_budget;
                } else {
                    /** กรณีลดงบประมาณ */
                    // TODO: 
                }
            } else {
                /** กรณีตัดยอดตามงบประมาณ */
                if($plan->sum_price < currencyToNumber($req['sum_price'])) {
                    /** กรณีขยายงบประมาณ */
                    $plan->remain_amount = $plan->remain_amount == 0 ? 1 : currencyToNumber($req['amount']);
    
                    if ($plan->remain_budget <= 0) {
                        /** กรณีใช้เกินงบ */
                        $plan->remain_budget = currencyToNumber($req['sum_price']) + $plan->remain_budget;
                    } else if ($plan->remain_budget > 0) {
                        /** กรณีเงินเหลือ */
                        $plan->remain_budget = currencyToNumber($req['sum_price']) - $used_budget;
                    }
                } else {
                    /** กรณีลดงบประมาณ */
                    // TODO: 
                }
            }

            $plan->amount       = currencyToNumber($req['amount']);
            $plan->sum_price    = currencyToNumber($req['sum_price']);

            if($plan->save()) {
                /** Update is_adjust field of found plans table */
                Plan::find($id)->update(['is_adjust' => 1]);

                /** Create new plan adjustment data */
                $adjustment = new PlanAdjustment;
                $adjustment->plan_id            = $req['plan_id'];
                $adjustment->adjust_type        = $req['adjust_type'];
                $adjustment->in_plan            = $oldPlan->plan->in_plan;
                $adjustment->price_per_unit     = $oldPlan->price_per_unit;
                $adjustment->unit_id            = $oldPlan->unit_id;
                $adjustment->amount             = $oldPlan->amount;
                $adjustment->sum_price          = $oldPlan->sum_price;
                $adjustment->remain_amount      = $oldPlan->remain_amount;
                $adjustment->remain_budget      = $oldPlan->remain_budget;
                $adjustment->remark             = $req['remark'];
                $adjustment->save();

                return [
                    "status"        => 1,
                    "message"       => 'Adjust plan data successfully!!'
                ];
            } else {
                return [
                    'status'    => 0,
                    'message'   => 'Something went wrong!!'
                ];
            }
        } catch (\Exception $ex) {
            return [
                "status"    => 0,
                "message"   => $ex->getMessage()
            ];
        }
    }

    public function inPlan(Request $req, $id)
    {
        try {
            /** Get old data of found plan */
            $oldPlan = PlanItem::with('plan')->where('plan_id', $id)->first();
            
            /** Update is_adjust field of found plans table */
            $plan = Plan::find($id);
            $plan->in_plan      = 'I';
            $plan->is_adjust    = 1;

            if($plan->save()) {
                /** Create new plan adjustment data */
                $adjustment = new PlanAdjustment;
                $adjustment->plan_id            = $id;
                $adjustment->adjust_type        = 2;
                $adjustment->in_plan            = $oldPlan->plan->in_plan;
                $adjustment->price_per_unit     = $oldPlan->price_per_unit;
                $adjustment->unit_id            = $oldPlan->unit_id;
                $adjustment->amount             = $oldPlan->amount;
                $adjustment->sum_price          = $oldPlan->sum_price;
                $adjustment->remain_amount      = $oldPlan->remain_amount;
                $adjustment->remain_budget      = $oldPlan->remain_budget;
                $adjustment->remark             = $req['remark'];
                $adjustment->save();

                return [
                    'status'    => 1,
                    'message'   => 'Adjust plan data successfully!!',
                    'plan'      => $plan
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

    public function getData(Request $req)
    {
        $matched = [];
        $arrStatus = [];
        $conditions = [];
        $pattern = '/^\<|\>|\&|\-/i';

        /** Get params from query string */
        $year       = $req->get('year');
        $type       = $req->get('type');
        $cate       = $req->get('cate');
        $faction    = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('faction')
                        : Auth::user()->memberOf->faction_id;
        $depart     = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('depart')
                        : Auth::user()->memberOf->depart_id;
        $division   = (Auth::user()->person_id == '1300200009261' || Auth::user()->person_id == '3249900388197' || Auth::user()->memberOf->duty_id == '1' || Auth::user()->memberOf->duty_id == '2' || Auth::user()->memberOf->depart_id == '4')
                        ? $req->get('division')
                        : '';
        $status     = $req->get('status');
        $approved   = $req->get('approved');
        $inStock    = $req->get('in_stock');
        $name       = $req->get('name');
        $price      = $req->get('price');
        $budget     = $req->get('budget');
        $inPlan     = $req->get('in_plan');
        $showAll    = $req->get('show_all');
        $haveSubitem = $req->get('have_subitem');
        $addon      = $req->get('addon');
        $adjust     = $req->get('adjust');

        if($status != '') {
            if (preg_match($pattern, $status, $matched) == 1) {
                $arrStatus = explode($matched[0], $status);

                if ($matched[0] != '-' && $matched[0] != '&') {
                    array_push($conditions, ['plans.status', $matched[0], $arrStatus[1]]);
                }
            } else {
                array_push($conditions, ['plans.status', '=', $status]);
            }
        }

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plansList = PlanItem::leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                        ->when(!empty($cate), function($q) use ($cate) {
                            $q->where('items.category_id', $cate);
                        })
                        ->when($inStock != '', function($q) use ($inStock) {
                            $q->where('items.in_stock', $inStock);
                        })
                        ->when(!empty($name), function($q) use ($name) {
                            $q->where(function($query) use ($name) {
                                $query->where('item_name', 'like', '%'.$name.'%');
                                $query->orWhere('en_name', 'like', '%'.$name.'%');
                            });
                        })
                        ->when(!empty($haveSubitem), function($q) use ($haveSubitem) {
                            $q->where('plan_items.have_subitem', $haveSubitem);
                        })
                        ->when(empty($showAll), function($q) use ($showAll) {
                            $q->where('plan_items.remain_amount', '>', 0);
                        })
                        ->when(!empty($price), function($q) use ($price) {
                            if ($price == '1') {
                                $q->where('plan_items.price_per_unit', '<', 10000);
                            } else {
                                $q->where('plan_items.price_per_unit', '>=', $price);
                            }
                        })
                        ->when($addon != '', function($q) use ($addon) {
                            if ($addon == '0') {
                                $q->whereNull('plan_items.addon_id');
                            } else {
                                $q->where('plan_items.addon_id', $addon);
                            }
                        })
                        ->pluck('plan_items.plan_id');

        $plans = Plan::with('budget','depart','division','planItem','adjustments')
                    ->with('planItem.unit','planItem.item','planItem.item.category')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('plans.year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($faction), function($q) use ($departsList, $depart, $cate) {
                        if ((Auth::user()->memberOf->depart_id == '39' && $cate == '3') || (Auth::user()->memberOf->depart_id == '65' && $cate == '4')) {

                        } else {
                            $q->whereIn('plans.depart_id', $departsList);
                        }
                    })
                    ->when(!empty($depart), function($q) use ($depart, $cate) {
                        if ((Auth::user()->memberOf->depart_id == '39' && $cate == '3') || (Auth::user()->memberOf->depart_id == '65' && $cate == '4')) {

                        } else {
                            $q->where('plans.depart_id', $depart);
                        }
                    })
                    ->when(!empty($division), function($q) use ($division) {
                        $q->where('plans.division_id', $division);
                    })
                    ->when(!empty($budget), function($q) use ($budget) {
                        $q->where('plans.budget_src_id', $budget);
                    })
                    ->when($approved != '', function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when($inPlan != '', function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->when(!empty($adjust), function($q) use ($adjust) {
                        $q->where('plans.is_adjust', $adjust);
                    })
                    ->when(count($conditions) > 0, function($q) use ($conditions) {
                        $q->where($conditions);
                    })
                    ->when(count($matched) > 0 && $matched[0] == '-', function($q) use ($arrStatus) {
                        $q->whereBetween('plans.status', $arrStatus);
                    })
                    ->when(!empty($cate), function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when($inStock != '', function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when(!empty($name), function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when(!empty($haveSubitem), function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when($addon != '', function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when(!empty($price), function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    })
                    ->when(empty($showAll), function($q) use ($plansList) {
                        $q->whereIn('plans.id', $plansList);
                    });

        return $plans;
    }

    public function excel(Request $req)
    {
        $planType = PlanType::find($req->get('type'));

        $fileName = 'plans-list-' . date('YmdHis') . '.xlsx';
        $options = [
            'plan_type_id' => $planType->id,
            'plan_type_name' => $planType->plan_type_name,
            'year' => $req->get('year'),
        ];
        
        exportExcel($fileName, 'exports.plans-list-excel', $this->getData($req)->get(), $options);
    }

    public function printForm(Request $req)
    {
        $plans = $this->getData($req)->get();
        $inStock = array_key_exists('in_stock', $req->all()) ? $req->get('in_stock') : '';

        $data = [
            "plans"     => $plans,
            "planType"  => PlanType::find($req->get('type')),
            "inStock"   => $inStock == '' ? '' : ($inStock == '0' ? 'นอกคลัง' : 'ในคลัง')
        ];

        /** Invoke helper function to return view of pdf instead of laravel's view to client */
        $paper = [
            'size'          => 'a4',
            'orientation'   => 'landscape'
        ];

        return renderPdf('forms.plans-list', $data, $paper, 'download');
    }
}
