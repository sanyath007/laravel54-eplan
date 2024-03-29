<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Faction;
use App\Models\Depart;
use App\Models\Division;
use App\Models\Person;
use App\Models\Plan;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\ProjectPayment;
use App\Models\PlanType;
use App\Models\ItemCategory;
use App\Models\Strategic;
use App\Models\Strategy;
use App\Models\Support;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.all', [

        ]);
    }

    public function projectSummary()
    {
        return view('reports.projects-summary', [

        ]);
    }

    public function getProjectSummary(Request $req)
    {
        $year = $req->get('year');

        $projects = Project::where('year', $year)->with('depart','payments')->get();

        return [
            'factions'  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            'projects'   => $projects
        ];
    }

    public function projectsList()
    {
        return view('reports.projects-list', [

        ]);
    }

    public function projectByFaction()
    {
        return view('reports.project-faction', [
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getProjectByFaction(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $approved   = $req->get('approved');

        $projects = \DB::table('projects')
                        ->select(
                            \DB::raw("projects.owner_depart as depart_id"),
                            \DB::raw("sum(case when (projects.project_type_id=1) then projects.total_budget end) as hos_budget"),
                            \DB::raw("count(case when (projects.project_type_id=1) then projects.id end) as hos_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=2) then projects.total_budget end) as cup_budget"),
                            \DB::raw("count(case when (projects.project_type_id=2) then projects.id end) as cup_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=3) then projects.total_budget end) as tam_budget"),
                            \DB::raw("count(case when (projects.project_type_id=3) then projects.id end) as tam_amount"),
                            \DB::raw("sum(projects.total_budget) as total_budget"),
                            \DB::raw("count(projects.id) as total_amount")
                        )
                        ->where('projects.year', $year)
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy('projects.owner_depart')
                        ->get();

        $payments = \DB::table('project_payments')
                        ->select(
                            \DB::raw("projects.owner_depart as depart_id"),
                            \DB::raw("sum(case when (projects.project_type_id=1) then project_payments.net_total end) as hos_paid"),
                            \DB::raw("sum(case when (projects.project_type_id=2) then project_payments.net_total end) as cup_paid"),
                            \DB::raw("sum(case when (projects.project_type_id=3) then project_payments.net_total end) as tam_paid"),
                            \DB::raw("sum(project_payments.net_total) as total_paid")
                        )
                        ->leftJoin('projects', 'projects.id', '=', 'project_payments.project_id')
                        ->where('projects.year', $year)
                        // ->when(!empty($type), function($q) use ($type) {
                        //     $q->where('projects.project_type_id', $type);
                        // })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy("projects.owner_depart")
                        ->get();

        return [
            'projects'  => $projects,
            'payments' => $payments,
            'departs'   => Depart::all(),
            'factions'  => Faction::whereNotIn('faction_id', [6,4,12])->get()
        ];
    }

    public function projectByDepart()
    {
        return view('reports.project-depart', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getProjectByDepart(Request $req)
    {
        /** Get params from query string */
        $faction    = $req->get('faction');
        $year       = $req->get('year');
        $approved   = $req->get('approved');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $projects = \DB::table('projects')
                        ->select(
                            \DB::raw("projects.owner_depart as depart_id"),
                            \DB::raw("sum(case when (projects.project_type_id=1) then projects.total_budget end) as hos_budget"),
                            \DB::raw("count(case when (projects.project_type_id=1) then projects.id end) as hos_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=2) then projects.total_budget end) as cup_budget"),
                            \DB::raw("count(case when (projects.project_type_id=2) then projects.id end) as cup_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=3) then projects.total_budget end) as tam_budget"),
                            \DB::raw("count(case when (projects.project_type_id=3) then projects.id end) as tam_amount"),
                            \DB::raw("sum(projects.total_budget) as total_budget"),
                            \DB::raw("count(projects.id) as total_amount")
                        )
                        ->where('projects.year', $year)
                        ->when(!empty($faction), function($q) use ($departsList) {
                            $q->whereIn('projects.owner_depart', $departsList);
                        })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy('projects.owner_depart')
                        ->get();

        return [
            'projects'  => $projects,
            'departs'   => Depart::all()
        ];
    }

    public function projectByStrategic()
    {
        return view('reports.project-strategic', [
            "strategics"  => Strategic::all(),
        ]);
    }

    public function getProjectByStrategic(Request $req)
    {
        /** Get params from query string */
        $strategic  = $req->get('strategic');
        $year       = $req->get('year');
        $approved   = $req->get('approved');

        $strategiesList = Strategy::where('strategic_id', $strategic)->pluck('id');

        $projects = \DB::table('projects')
                        ->select(
                            "projects.strategy_id",
                            "strategies.strategy_name",
                            \DB::raw("count(case when (projects.project_type_id=1) then projects.id end) as hos_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=1) then projects.total_budget end) as hos_budget"),
                            \DB::raw("count(case when (projects.project_type_id=2) then projects.id end) as cup_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=2) then projects.total_budget end) as cup_budget"),
                            \DB::raw("count(case when (projects.project_type_id=3) then projects.id end) as tam_amount"),
                            \DB::raw("sum(case when (projects.project_type_id=3) then projects.total_budget end) as tam_budget"),
                            \DB::raw("count(projects.id) as total_amount"),
                            \DB::raw("sum(projects.total_budget) as total_budget")
                        )
                        ->leftJoin('strategies', 'strategies.id', '=', 'projects.strategy_id')
                        ->leftJoin('strategics', 'strategics.id', '=', 'strategies.strategic_id')
                        ->where('projects.year', $year)
                        ->when(!empty($strategic), function($q) use ($strategiesList) {
                            $q->whereIn('projects.strategy_id', $strategiesList);
                        })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy('projects.strategy_id')
                        ->groupBy('strategies.strategy_name')
                        ->get();

        return [
            'projects'      => $projects,
            'strategies'    => Strategy::all()
        ];
    }

    public function projectByQuarter()
    {
        return view('reports.project-quarter', [
            "strategics"    => Strategic::all(),
            "projectTypes"  => ProjectType::all(),
        ]);
    }

    public function getProjectByQuarter(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $type       = $req->get('type');
        $strategic  = $req->get('strategic');
        $approved   = $req->get('approved');

        $strategiesList = Strategy::where('strategic_id', $strategic)->pluck('id');

        $projects = \DB::table('projects')
                        ->select(
                            "projects.strategy_id",
                            "strategies.strategy_name",
                            \DB::raw("count(case when (projects.start_month in ('10','11','12')) then projects.id end) as q1_amt"),
                            \DB::raw("sum(case when (projects.start_month in ('10','11','12')) then projects.total_budget end) as q1_bud"),
                            \DB::raw("count(case when (projects.start_month in ('01','02','03')) then projects.id end) as q2_amt"),
                            \DB::raw("sum(case when (projects.start_month in ('01','02','03')) then projects.total_budget end) as q2_bud"),
                            \DB::raw("count(case when (projects.start_month in ('04','05','06')) then projects.id end) as q3_amt"),
                            \DB::raw("sum(case when (projects.start_month in ('04','05','06')) then projects.total_budget end) as q3_bud"),
                            \DB::raw("count(projects.id) as total_amt"),
                            \DB::raw("sum(projects.total_budget) as total_bud")
                        )
                        ->leftJoin('strategies', 'strategies.id', '=', 'projects.strategy_id')
                        ->leftJoin('strategics', 'strategics.id', '=', 'strategies.strategic_id')
                        ->where('projects.year', $year)
                        ->when(!empty($strategic), function($q) use ($strategiesList) {
                            $q->whereIn('projects.strategy_id', $strategiesList);
                        })
                        ->when(!empty($type), function($q) use ($type) {
                            $q->where('projects.project_type_id', $type);
                        })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy('projects.strategy_id')
                        ->groupBy('strategies.strategy_name')
                        ->get();

        return [
            'projects'      => $projects,
            'strategies'    => Strategy::all()
        ];
    }

    public function projectProcessByQuarter()
    {
        return view('reports.project-process-quarter', [
            "strategics"    => Strategic::all(),
            "projectTypes"  => ProjectType::all(),
        ]);
    }

    public function getProjectProcessByQuarter(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $type       = $req->get('type');
        $strategic  = $req->get('strategic');
        $approved   = $req->get('approved');

        $strategiesList = Strategy::where('strategic_id', $strategic)->pluck('id');

        $projects = \DB::table('projects')
                        ->select(
                            "projects.strategy_id",
                            "strategies.strategy_name",
                            \DB::raw("sum(case when (projects.start_month in ('10','11','12')) then projects.total_budget end) as q1_bud"),
                            \DB::raw("sum(case when (projects.start_month in ('01','02','03')) then projects.total_budget end) as q2_bud"),
                            \DB::raw("sum(case when (projects.start_month in ('04','05','06')) then projects.total_budget end) as q3_bud"),
                            \DB::raw("sum(case when (projects.start_month in ('07','08','09')) then projects.total_budget end) as q4_bud"),
                            \DB::raw("sum(projects.total_budget) as total_bud")
                        )
                        ->leftJoin('strategies', 'strategies.id', '=', 'projects.strategy_id')
                        ->leftJoin('strategics', 'strategics.id', '=', 'strategies.strategic_id')
                        ->where('projects.year', $year)
                        ->when(!empty($strategic), function($q) use ($strategiesList) {
                            $q->whereIn('projects.strategy_id', $strategiesList);
                        })
                        ->when(!empty($type), function($q) use ($type) {
                            $q->where('projects.project_type_id', $type);
                        })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->groupBy('projects.strategy_id')
                        ->groupBy('strategies.strategy_name')
                        ->get();

        $payments = \DB::table('project_payments')
                    ->select(
                        "projects.strategy_id",
                        \DB::raw("sum(case when (projects.start_month in ('10','11','12')) then project_payments.net_total end) as q1_amt"),
                        \DB::raw("sum(case when (projects.start_month in ('01','02','03')) then project_payments.net_total end) as q2_amt"),
                        \DB::raw("sum(case when (projects.start_month in ('04','05','06')) then project_payments.net_total end) as q3_amt"),
                        \DB::raw("sum(case when (projects.start_month in ('07','08','09')) then project_payments.net_total end) as q4_amt"),
                        \DB::raw("sum(project_payments.net_total) as total_amt")
                    )
                    ->leftJoin('projects', 'projects.id', '=', 'project_payments.project_id')
                    ->where('projects.year', $year)
                    ->when(!empty($strategic), function($q) use ($strategiesList) {
                        $q->whereIn('projects.strategy_id', $strategiesList);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('projects.project_type_id', $type);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('projects.approved', $approved);
                    })
                    ->groupBy("projects.strategy_id")
                    ->get();

        return [
            'projects'      => $projects,
            'payments'      => $payments,
            'strategies'    => Strategy::all()
        ];
    }

    public function projectStrategyByQuarter($strategy)
    {
        return view('reports.project-strategy-quarter', [
            "strategics"    => Strategic::all(),
            "projectTypes"  => ProjectType::all(),
            'strategy'      => Strategy::find($strategy)
        ]);
    }

    public function getProjectStrategyByQuarter(Request $req, $strategy)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $type       = $req->get('type');
        $strategic  = $req->get('strategic');
        $approved   = $req->get('approved');

        $strategiesList = Strategy::where('strategic_id', $strategic)->pluck('id');

        $projects = Project::with('kpi','depart','owner','budgetSrc','timeline','payments')
                        ->where('projects.year', $year)
                        ->when(!empty($strategic), function($q) use ($strategiesList) {
                            $q->whereIn('projects.strategy_id', $strategiesList);
                        })
                        ->when(!empty($strategy), function($q) use ($strategy) {
                            $q->where('strategy_id', $strategy);
                        })
                        ->when(!empty($type), function($q) use ($type) {
                            $q->where('projects.project_type_id', $type);
                        })
                        ->when(!empty($approved), function($q) use ($approved) {
                            $q->where('projects.approved', $approved);
                        })
                        ->get();

        $payments = \DB::table('project_payments')
                    ->select(
                        "project_payments.project_id",
                        \DB::raw("sum(project_payments.net_total) as total_paid")
                    )
                    ->leftJoin('projects', 'projects.id', '=', 'project_payments.project_id')
                    ->where('projects.year', $year)
                    ->when(!empty($strategic), function($q) use ($strategiesList) {
                        $q->whereIn('projects.strategy_id', $strategiesList);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('projects.project_type_id', $type);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('projects.approved', $approved);
                    })
                    ->groupBy("project_payments.project_id")
                    ->get();

        return [
            'projects'      => $projects,
            'payments'      => $payments,
            'strategies'    => Strategy::all(),
        ];
    }

    public function planByFaction()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-faction', [
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getPlanByFaction(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $price      = $req->get('price');
        $inPlan     = $req->get('in_plan');
        $approved   = $req->get('approved');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.depart_id',
                        \DB::raw("sum(case when (plans.plan_type_id=1) then plan_items.sum_price end) as asset"),
                        \DB::raw("sum(case when (plans.plan_type_id=2) then plan_items.sum_price end) as material"),
                        \DB::raw("sum(case when (plans.plan_type_id=3) then plan_items.sum_price end) as service"),
                        \DB::raw("sum(case when (plans.plan_type_id=4) then plan_items.sum_price end) as construct"),
                        \DB::raw("sum(plan_items.sum_price) as total")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->where('plans.year', $year)
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->groupBy('plans.depart_id')
                    ->get();

        return [
            'plans'     => $plans,
            'departs'   => Depart::all(),
            'factions'  => Faction::whereNotIn('faction_id', [6,4,12])->get()
        ];
    }

    public function planByDepart()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-depart', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getPlanByDepart(Request $req)
    {
        /** Get params from query string */
        $faction    = $req->get('faction');
        $year       = $req->get('year');
        $price      = $req->get('price');
        $inPlan     = $req->get('in_plan');
        $approved   = $req->get('approved');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.depart_id',
                        \DB::raw("sum(case when (plans.plan_type_id=1) then plan_items.sum_price end) as asset"),
                        \DB::raw("sum(case when (plans.plan_type_id=1) then plan_items.remain_budget end) as asset_budget"),
                        \DB::raw("sum(case when (plans.plan_type_id=2) then plan_items.sum_price end) as material"),
                        \DB::raw("sum(case when (plans.plan_type_id=2) then plan_items.remain_budget end) as material_budget"),
                        \DB::raw("sum(case when (plans.plan_type_id=3) then plan_items.sum_price end) as service"),
                        \DB::raw("sum(case when (plans.plan_type_id=3) then plan_items.remain_budget end) as service_budget"),
                        \DB::raw("sum(case when (plans.plan_type_id=4) then plan_items.sum_price end) as construct"),
                        \DB::raw("sum(case when (plans.plan_type_id=4) then plan_items.remain_budget end) as construct_budget"),
                        \DB::raw("sum(plan_items.sum_price) as total"),
                        \DB::raw("sum(plan_items.remain_budget) as total_budget")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->where('plans.year', $year)
                    ->when(!empty($faction), function($q) use ($departsList) {
                        $q->whereIn('plans.depart_id', $departsList);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->groupBy('plans.depart_id')
                    ->get();

        return [
            'plans'     => $plans,
            'departs'   => Depart::all()
        ];
    }

    public function planByItem()
    {
        return view('reports.plan-item', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
            "categories"    => ItemCategory::all(),
        ]);
    }

    public function getPlanByItem(Request $req)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $type       = $req->get('type');
        $cate       = $req->get('cate');
        $price      = $req->get('price');
        $approved   = $req->get('approved');
        $inPlan     = $req->get('in_plan');
        $isFixcost  = $req->get('isFixcost');
        $sort       = empty($req->get('sort')) ? 'sum_price' : $req->get('sort');

        $plans = \DB::table('plans')
                    ->select(
                        'plan_items.item_id',
                        'items.item_name',
                        \DB::raw("sum(plan_items.amount) as amount"),
                        \DB::raw("sum(plan_items.sum_price) as sum_price"),
                        \DB::raw("sum(plan_items.remain_amount) as remain_amount"),
                        \DB::raw("sum(plan_items.remain_budget) as remain_budget")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($cate), function($q) use ($cate) {
                        $q->where('items.category_id', $cate);
                    })
                    ->when(!empty($isFixcost), function($q) use ($isFixcost) {
                        $q->where('items.is_fixcost', $isFixcost);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->when(!empty($price), function($q) use ($price) {
                        if ($price == 1) {
                            $q->where('plan_items.price_per_unit', '>=', 10000);
                        } else {
                            $q->where('plan_items.price_per_unit', '<', 10000);
                        }
                    })
                    ->groupBy('plan_items.item_id')
                    ->groupBy('items.item_name')
                    ->when(!empty($isFixcost), function($q) use ($sort) {
                        $q->orderBy("plans.depart_id");
                        $q->orderByRaw("sum(plan_items." .$sort. ") DESC");
                    })
                    ->when(empty($isFixcost), function($q) use ($sort) {
                        $q->orderByRaw("sum(plan_items." .$sort. ") DESC");
                        $q->orderBy("plans.plan_type_id");
                        $q->orderBy("items.item_name", "DESC");
                    })
                    ->get();

        return [
            'plans' => $plans,
        ];
    }

    public function planByType()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-type', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
        ]);
    }

    public function getPlanByType(Request $req)
    {
        /** Get params from query string */
        $faction    = $req->get('faction');
        $year       = $req->get('year');
        $type       = $req->get('type');
        $price      = $req->get('price');
        $approved   = $req->get('approved');
        $inPlan     = $req->get('in_plan');
        $sort       = empty($req->get('sort')) ? 'sum_price' : $req->get('sort');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plans = \DB::table('plans')
                    ->select(
                        'items.category_id',
                        \DB::raw("sum(plan_items.amount) as amount"),
                        \DB::raw("sum(plan_items.sum_price) as sum_price"),
                        \DB::raw("sum(plan_items.remain_amount) as remain_amount"),
                        \DB::raw("sum(plan_items.remain_budget) as remain_budget")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->when(!empty($price), function($q) use ($price) {
                        if ($price == 1) {
                            $q->where('plan_items.price_per_unit', '>=', 10000);
                        } else {
                            $q->where('plan_items.price_per_unit', '<', 10000);
                        }
                    })
                    ->groupBy('items.category_id')
                    ->orderByRaw("sum(plan_items." .$sort. ") DESC")
                    ->get();

        $categories = ItemCategory::all();
                        // ->when(!empty($type), function($q) use ($type) {
                        //     $q->where('plan_type_id', $type);
                        // })->get();

        return [
            'plans'         => $plans,
            'categories'    => $categories
        ];
    }

    public function planByQuarter()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-quarter', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
        ]);
    }

    public function getPlanByQuarter(Request $req)
    {
        /** Get params from query string */
        $faction    = $req->get('faction');
        $year       = $req->get('year');
        $type       = $req->get('type');
        $price      = $req->get('price');
        $inPlan     = $req->get('in_plan');
        $approved   = $req->get('approved');
        $sort       = empty($req->get('sort')) ? 'sum_price' : $req->get('sort');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plans = \DB::table('plans')
                    ->select(
                        'items.category_id',
                        \DB::raw("sum(case when (plans.start_month in ('10','11','12')) then plan_items.amount end) as q1_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('10','11','12')) then plan_items.sum_price end) as q1_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('01','02','03')) then plan_items.amount end) as q2_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('01','02','03')) then plan_items.sum_price end) as q2_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('04','05','06')) then plan_items.amount end) as q3_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('04','05','06')) then plan_items.sum_price end) as q3_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('07','08','09')) then plan_items.amount end) as q4_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('07','08','09')) then plan_items.sum_price end) as q4_sum"),
                        \DB::raw("sum(plan_items.amount) as total_amt"),
                        \DB::raw("sum(plan_items.sum_price) as total_sum")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->when(!empty($price), function($q) use ($price) {
                        if ($price == 1) {
                            $q->where('plan_items.price_per_unit', '>=', 10000);
                        } else {
                            $q->where('plan_items.price_per_unit', '<', 10000);
                        }
                    })
                    ->groupBy('items.category_id')
                    ->orderByRaw("sum(plan_items." .$sort. ") DESC")
                    ->get();

        $categories = ItemCategory::all();

        return [
            'plans'         => $plans,
            'categories'    => $categories
        ];
    }

    public function planProcessByQuarter()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-process-quarter', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
        ]);
    }

    public function getPlanProcessByQuarter(Request $req)
    {
        /** Get params from query string */
        $faction    = $req->get('faction');
        $year       = $req->get('year');
        $type       = $req->get('type');
        $price      = $req->get('price');
        $inPlan     = $req->get('in_plan');
        $approved   = $req->get('approved');
        $sort       = empty($req->get('sort')) ? 'sum_price' : $req->get('sort');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plans = \DB::table('plans')
                    ->select(
                        'items.category_id',
                        \DB::raw("sum(case when (plans.start_month in ('10','11','12')) then plan_items.sum_price end) as q1_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('10','11','12') and plans.id in (select plan_id from support_details where status in (2,3,4,5,6))) then support_details.sum_price end) as q1_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('01','02','03')) then plan_items.sum_price end) as q2_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('01','02','03') and plans.id in (select plan_id from support_details where status in (2,3,4,5,6))) then support_details.sum_price end) as q2_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('04','05','06')) then plan_items.sum_price end) as q3_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('04','05','06') and plans.id in (select plan_id from support_details where status in (2,3,4,5,6))) then support_details.sum_price end) as q3_amt"),
                        \DB::raw("sum(case when (plans.start_month in ('07','08','09')) then plan_items.sum_price end) as q4_sum"),
                        \DB::raw("sum(case when (plans.start_month in ('07','08','09') and plans.id in (select plan_id from support_details where status in (2,3,4,5,6))) then support_details.sum_price end) as q4_amt"),
                        \DB::raw("sum(plan_items.sum_price) as total_sum"),
                        \DB::raw("sum(case when (plans.id in (select plan_id from support_details where status in (2,3,4,5,6))) then plan_items.sum_price end) as total_amt")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->leftJoin('support_details', 'support_details.plan_id', '=', 'plans.id')
                    ->where('plans.year', $year)
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->when(!empty($inPlan), function($q) use ($inPlan) {
                        $q->where('plans.in_plan', $inPlan);
                    })
                    ->when(!empty($price), function($q) use ($price) {
                        if ($price == 1) {
                            $q->where('plan_items.price_per_unit', '>=', 10000);
                        } else {
                            $q->where('plan_items.price_per_unit', '<', 10000);
                        }
                    })
                    ->groupBy('items.category_id')
                    ->orderByRaw("sum(plan_items." .$sort. ") DESC")
                    ->get();

        $categories = ItemCategory::all();

        return [
            'plans'         => $plans,
            'categories'    => $categories
        ];
    }

    public function planProcessByDetails(Request $req, $type)
    {
        $quarter    = $req->get('quarter');
        $cate       = $req->get('cate');

        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-process-details', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planType"  => PlanType::find($type),
            "categories" => ItemCategory::where('plan_type_id', $type)->get(),
            "type"      => $type,
            "quarter"   => $quarter,
            "cate"      => $cate
        ]);
    }

    public function getPlanProcessByDetails(Request $req, $type)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $faction    = $req->get('faction');
        $quarter    = $req->get('quarter');
        $cate       = $req->get('cate');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plansList = Plan::when(!empty($quarter), function($q) use ($quarter) {
                                if ($quarter == '1') {
                                    $q->whereIn('start_month', ['10','11','12']);
                                } else if ($quarter == '2') {
                                    $q->whereIn('start_month', ['01','02','03']);
                                } else if ($quarter == '3') {
                                    $q->whereIn('start_month', ['04','05','06']);
                                } else {
                                    $q->whereIn('start_month', ['07','08','09']);
                                }
                            })
                            // ->when(!empty($approved), function($q) use ($approved) {
                            //     $q->where('plans.approved', $approved);
                            // })
                            ->pluck('id');

        $supportsList = Support::where('plan_type_id', $type)
                            ->when(!empty($year), function($q) use ($year) {
                                $q->where('year', $year);
                            })
                            ->pluck('id');

        $plans = \DB::table('support_details')
                    ->select(
                        'support_details.id','support_details.support_id','support_details.plan_id',
                        'items.item_name','support_details.desc','support_details.price_per_unit',
                        'support_details.amount','support_details.sum_price','plan_items.calc_method'
                    )
                    ->leftJoin('plan_items', 'support_details.plan_id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->whereIn('support_details.status', [2,3,4,5,6])
                    ->whereIn('support_details.support_id', $supportsList)
                    ->whereIn('support_details.plan_id', $plansList)
                    ->when(!empty($cate), function($q) use ($cate) {
                        $q->where('items.category_id', $cate);
                    })
                    ->orderByRaw("items.item_name")
                    ->get();

        return [
            'plans' => $plans,
        ];
    }

    public function planProcessByRequests(Request $req, $type)
    {
        $quarter    = $req->get('quarter');
        $cate       = $req->get('cate');

        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.plan-process-requests', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planType"  => PlanType::find($type),
            "categories" => ItemCategory::where('plan_type_id', $type)->get(),
            "type"      => $type,
            "quarter"   => $quarter,
            "cate"      => $cate
        ]);
    }

    public function getPlanProcessByRequests(Request $req, $type)
    {
        /** Get params from query string */
        $year       = $req->get('year');
        $faction    = $req->get('faction');
        $quarter    = $req->get('quarter');
        $cate       = $req->get('cate');

        $departsList = Depart::where('faction_id', $faction)->pluck('depart_id');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.id','plans.plan_no',
                        'items.item_name','plan_items.spec','plan_items.price_per_unit',
                        'plan_items.amount','plan_items.sum_price','plan_items.calc_method'
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('approved', 'A')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('plans.plan_type_id', $type);
                    })
                    ->when(!empty($quarter), function($q) use ($quarter) {
                        if ($quarter == '1') {
                            $q->whereIn('plans.start_month', ['10','11','12']);
                        } else if ($quarter == '2') {
                            $q->whereIn('plans.start_month', ['01','02','03']);
                        } else if ($quarter == '3') {
                            $q->whereIn('plans.start_month', ['04','05','06']);
                        } else {
                            $q->whereIn('plans.start_month', ['07','08','09']);
                        }
                    })
                    ->when(!empty($cate), function($q) use ($cate) {
                        $q->where('items.category_id', $cate);
                    })
                    ->orderByRaw("items.item_name")
                    ->get();

        return [
            'plans' => $plans,
        ];
    }

    public function assetByFaction()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.asset-faction', [
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getAssetByFaction(Request $req)
    {
        /** Get params from query string */
        $year = $req->get('year');
        $approved = $req->get('approved');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.depart_id',
                        \DB::raw("sum(case when (items.category_id=1) then plan_items.sum_price end) as vehicle"),
                        \DB::raw("sum(case when (items.category_id=2) then plan_items.sum_price end) as office"),
                        \DB::raw("sum(case when (items.category_id=3) then plan_items.sum_price end) as computer"),
                        \DB::raw("sum(case when (items.category_id=4) then plan_items.sum_price end) as medical"),
                        \DB::raw("sum(case when (items.category_id=5) then plan_items.sum_price end) as home"),
                        \DB::raw("sum(case when (items.category_id=6) then plan_items.sum_price end) as construct"),
                        \DB::raw("sum(case when (items.category_id=7) then plan_items.sum_price end) as agriculture"),
                        \DB::raw("sum(case when (items.category_id=8) then plan_items.sum_price end) as ads"),
                        \DB::raw("sum(case when (items.category_id=9) then plan_items.sum_price end) as electric"),
                        \DB::raw("sum(case when (items.category_id between 1 and 9) then plan_items.sum_price end) as total")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->groupBy('plans.depart_id')
                    ->get();

        return [
            'plans'     => $plans,
            'departs'   => Depart::all(),
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
        ];
    }

    public function assetByDepart()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.asset-depart', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getAssetByDepart(Request $req)
    {
        /** Get params from query string */
        // $faction    = Auth::user()->memberOf->duty_id == 2
        //                 ? Auth::user()->memberOf->faction_id
        //                 : $req->get('faction');
        $year = $req->get('year');
        $approved = $req->get('approved');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.depart_id',
                        \DB::raw("sum(case when (items.category_id=1) then plan_items.sum_price end) as vehicle"),
                        \DB::raw("sum(case when (items.category_id=2) then plan_items.sum_price end) as office"),
                        \DB::raw("sum(case when (items.category_id=3) then plan_items.sum_price end) as computer"),
                        \DB::raw("sum(case when (items.category_id=4) then plan_items.sum_price end) as medical"),
                        \DB::raw("sum(case when (items.category_id=5) then plan_items.sum_price end) as home"),
                        \DB::raw("sum(case when (items.category_id=6) then plan_items.sum_price end) as construct"),
                        \DB::raw("sum(case when (items.category_id=7) then plan_items.sum_price end) as agriculture"),
                        \DB::raw("sum(case when (items.category_id=8) then plan_items.sum_price end) as ads"),
                        \DB::raw("sum(case when (items.category_id=9) then plan_items.sum_price end) as electric"),
                        \DB::raw("sum(case when (items.category_id between 1 and 9) then plan_items.sum_price end) as total")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->groupBy('plans.depart_id')
                    ->get();

        return [
            'plans'     => $plans,
            'departs'   => Depart::all()
        ];
    }

    public function materialByDepart()
    {
        $depart = '';
        if (Auth::user()->memberOf->duty_id == 2) {
            $depart = Auth::user()->memberOf->depart_id;
        }

        return view('reports.material-depart', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get()
        ]);
    }

    public function getMaterialByDepart(Request $req)
    {
        /** Get params from query string */
        // $faction    = Auth::user()->memberOf->duty_id == 2
        //                 ? Auth::user()->memberOf->faction_id
        //                 : $req->get('faction');
        $year = $req->get('year');
        $approved = $req->get('approved');

        $plans = \DB::table('plans')
                    ->select(
                        'plans.depart_id',
                        \DB::raw("sum(case when (items.category_id=10) then plan_items.sum_price end) as medical"),
                        \DB::raw("sum(case when (items.category_id=11) then plan_items.sum_price end) as science"),
                        \DB::raw("sum(case when (items.category_id=12) then plan_items.sum_price end) as dent"),
                        \DB::raw("sum(case when (items.category_id=13) then plan_items.sum_price end) as office"),
                        \DB::raw("sum(case when (items.category_id=14) then plan_items.sum_price end) as computer"),
                        \DB::raw("sum(case when (items.category_id=15) then plan_items.sum_price end) as home"),
                        \DB::raw("sum(case when (items.category_id=16) then plan_items.sum_price end) as clothes"),
                        \DB::raw("sum(case when (items.category_id=17) then plan_items.sum_price end) as fuel"),
                        \DB::raw("sum(case when (items.category_id=18) then plan_items.sum_price end) as sticker"),
                        \DB::raw("sum(case when (items.category_id=19) then plan_items.sum_price end) as electric"),
                        \DB::raw("sum(case when (items.category_id=20) then plan_items.sum_price end) as vehicle"),
                        \DB::raw("sum(case when (items.category_id=21) then plan_items.sum_price end) as ads"),
                        \DB::raw("sum(case when (items.category_id=22) then plan_items.sum_price end) as construct"),
                        \DB::raw("sum(case when (items.category_id=23) then plan_items.sum_price end) as agriculture"),
                        \DB::raw("sum(case when (items.category_id between 10 and 23) then plan_items.sum_price end) as total")
                    )
                    ->leftJoin('plan_items', 'plans.id', '=', 'plan_items.plan_id')
                    ->leftJoin('items', 'items.id', '=', 'plan_items.item_id')
                    ->where('plans.year', $year)
                    ->when(!empty($approved), function($q) use ($approved) {
                        $q->where('plans.approved', $approved);
                    })
                    ->groupBy('plans.depart_id')
                    ->get();

        return [
            'plans'     => $plans,
            'departs'   => Depart::all()
        ];
    }

    public function orderCompareSupport()
    {
        return view('reports.order-compare-support', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
        ]);
    }

    public function getOrderCompareSupport(Request $req)
    {
        $year = $req->get('year');
        $month = $req->get('month');
        $type = $req->get('type');

        $supports = \DB::table('supports')
                    ->select(
                        'supports.category_id',
                        'item_categories.name',
                        \DB::raw("count(case when (supports.status in (1,2,3,4,5,6,99)) then supports.id end) as sent"),
                        \DB::raw("count(case when (supports.status in (2,3,4,5,6,99)) then supports.id end) as received"),
                        \DB::raw("count(case when (supports.status in (3,4,5,6,99)) then supports.id end) as ordered")
                    )
                    ->leftJoin('item_categories', 'supports.category_id', '=', 'item_categories.id')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('supports.plan_type_id', $type);
                    })
                    ->when(!empty($month), function($q) use ($month) {
                        $sdate = $month. '-01';
                        $edate = date('Y-m-t', strtotime($sdate));

                        $q->whereBetween('supports.sent_date', [$sdate, $edate]);
                    })
                    ->groupBy('supports.category_id')
                    ->groupBy('item_categories.name')
                    ->get();

        return [
            "supports"  => $supports,
        ];
    }

    public function orderBackwardMonth()
    {
        return view('reports.order-backward-month', [
            "factions"  => Faction::whereNotIn('faction_id', [6,4,12])->get(),
            "departs"   => Depart::orderBy('depart_name', 'ASC')->get(),
            "planTypes" => PlanType::all(),
        ]);
    }

    public function getOrderBackwardMonth(Request $req)
    {
        $year = $req->get('year');
        $month = $req->get('month');
        $type = $req->get('type');

        $orders = \DB::table('orders')
                    ->select(
                        'orders.category_id',
                        'item_categories.name',
                        \DB::raw("count(orders.id) as all_po"),
                        \DB::raw("sum(orders.net_total) as all_net"),
                        \DB::raw("count(case when(datediff(date(orders.created_at), orders.po_date) > 30) then orders.id end) as back_po"),
                        \DB::raw("sum(case when(datediff(date(orders.created_at), orders.po_date) > 30) then orders.net_total end) as back_net")
                    )
                    ->leftJoin('item_categories', 'orders.category_id', '=', 'item_categories.id')
                    ->when(!empty($year), function($q) use ($year) {
                        $q->where('orders.year', $year);
                    })
                    ->when(!empty($type), function($q) use ($type) {
                        $q->where('orders.plan_type_id', $type);
                    })
                    ->when(!empty($month), function($q) use ($month) {
                        $sdate = $month. '-01';
                        $edate = date('Y-m-t', strtotime($sdate));

                        $q->whereBetween('orders.po_date', [$sdate, $edate]);
                    })
                    ->groupBy('orders.category_id')
                    ->groupBy('item_categories.name')
                    ->get();

        return [
            "orders"  => $orders,
        ];
    }
}
