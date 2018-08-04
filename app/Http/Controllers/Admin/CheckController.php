<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Helpers\LeaveHelper;
use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;
use App\Models\Staff;
use App\Models\Check;
use App\Models\Profile;

class CheckController extends Controller
{
    private $OPERATOR = [
        0 => "=",
        1 => ">=",
        2 => "<=",
    ];

    private $CHECK_ENG_TYPE = [
        1  => "personal_leave",
        2  => "annual_leave",
        3  => "official_leave",
        4  => 'sick_leave',
        5  => 'online',
        6  => 'late',
        7  => 'mourning_leave',
        8  => 'maternity_leave',
        9  => 'paternity_leave',
        10 => 'marriage_leave',
    ];
    public function export_statistic(Request $request)
    {
        switch ($request->input('action_type')) {

            case 'lookup':
                if ($request->has(['date-range', 'id'])) {
                    $from = explode(" - ", $request->input('date-range'))[0];
                    $to =   explode(" - ", $request->input('date-range'))[1];

                    $rows  = $this->getStatisticRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value', 'type']));
                }
                break;

            case 'export':
                return $this->exportST($request);
                break;
        }

        $rows = isset($rows) ? $rows : [];
        //form options
        $options['name'] =Staff::all()->pluck('name', 'id')->toArray();
        $options['operators'] = array(
            0   => "等於",
            1   => "大於等於",
            2   => "小於等於",
        );
        $options['type'] = array(
            Check::TYPE_PERSONAL_LEAVE  => $this->CHECK_TYPE[Check::TYPE_PERSONAL_LEAVE],
            Check::TYPE_ANNUAL_LEAVE    => $this->CHECK_TYPE[Check::TYPE_ANNUAL_LEAVE],
            Check::TYPE_OFFICIAL_LEAVE  => $this->CHECK_TYPE[Check::TYPE_OFFICIAL_LEAVE],
            Check::TYPE_SICK_LEAVE      => $this->CHECK_TYPE[Check::TYPE_SICK_LEAVE],
            Check::TYPE_ONLINE          => $this->CHECK_TYPE[Check::TYPE_ONLINE],
            Check::TYPE_MOURNING_LEAVE  => $this->CHECK_TYPE[Check::TYPE_MOURNING_LEAVE],
            Check::TYPE_MATERNITY_LEAVE => $this->CHECK_TYPE[Check::TYPE_MATERNITY_LEAVE],
            Check::TYPE_PATERNITY_LEAVE => $this->CHECK_TYPE[Check::TYPE_PATERNITY_LEAVE],
            Check::TYPE_MARRIAGE_LEAVE  => $this->CHECK_TYPE[Check::TYPE_MARRIAGE_LEAVE],
        );

        return view('admin.pages.check.export_page', compact('options', 'rows'));
    }

    private function exportST(Request $request)
    {
        $messages = [
            'id.required'   => '請選擇姓名',
            'type.required' => '請選擇類別',
        ];
        $validator = Validator::make($request->all(), [
            'id'   => 'required',
            'type' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->route('admin.check.export_page')->withErrors($validator->errors());
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $from = explode(" - ", $request->input('date-range'))[0];
        $to   = explode(" - ", $request->input('date-range'))[1];

        //第一列
        $columns = array("姓名", "日期");
        foreach ($request->input('type') as $type) {
            $columns[] = $this->CHECK_TYPE[$type]."時數";
        }

        $all_rows  = $this->getStatisticRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value', 'type']));
        $callback = function() use ($columns, $all_rows)
        {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach($all_rows as $row) {
                if (!empty($row)) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers); 
    }

    private function getStatisticRows($from, $to, $id, $conditions)
    {
        $mysql =
            "SELECT name, DATE(checkin_at) as date";

        foreach ($conditions['type'] as $type) {
            $mysql .= ", SUM(IF(type = ".$type.",IF(checkin_at <= DATE_ADD(DATE(checkin_at), INTERVAL 12 HOUR) && checkout_at >= DATE_ADD(DATE(checkin_at), INTERVAL 13 HOUR), TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at) - 60, TIMESTAMPDIFF(MINUTE,checkin_at,checkout_at)), 0)) as ".$this->CHECK_ENG_TYPE[$type]."_time";
        }

        $mysql =
            $mysql." FROM ( SELECT s.name, s.staff_code, c.checkin_at, c.checkout_at, c.type FROM checks AS c left join  staffs AS s on s.id = c.staff_id
            WHERE c.staff_id IN (".implode(',', $id).")"
            ." AND checkin_at >= '".$from." 00:00:00'"
            ." AND checkout_at <= '".date('Y-m-d', strtotime('+1 day', strtotime($to)))." 00:00:00'"
            ." AND type != 0"
            ." ORDER BY DATE(c.checkin_at), staff_code) AS temp"
            ." GROUP BY staff_code,date";

        if (array_key_exists('has', $conditions) && $conditions['has']['work_time']) {
            $mysql =
                $mysql." HAVING SUM(TIMESTAMPDIFF(HOUR,checkin_at,checkout_at)) ".$this->OPERATOR[$conditions['op']['work_time']]." ".$conditions['value']['work_time']."\n";
        }

        $rows = collect(DB::select($mysql))->map(function ($item) {
            return json_decode(json_encode($item), true);
        })->toArray();

        $rows = array_where($rows, function($value,$key) {
            return array_sum(array_except($value, ['date', 'name'])) != 0;
        });
        return $rows;
    }

    public function export_check(Request $request)
    {
        switch ($request->input('action_type')) {

            case 'lookup':
                if ($request->has(['date-range', 'id'])) {
                    $from = explode(" - ", $request->input('date-range'))[0];
                    $to =   explode(" - ", $request->input('date-range'))[1];

                    $rows  = $this->getCheckTimeRows($from , $to, $request->input('id'));
                }
                break;

            case 'export':
                return $this->exportCheck($request);
                break;
        }

        $rows = isset($rows) ? $rows : [];

        //form options
        $options['name'] =Staff::all()->pluck('name', 'id')->toArray();

        return view('admin.pages.check.export_check_page', compact('options', 'rows'));
    }

    private function exportCheck(Request $request)
    {
        $messages = [
            'id.required'   => '請選擇姓名',
        ];
        $validator = Validator::make($request->all(), [
            'id'   => 'required',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->route('admin.check.export_check_page')->withErrors($validator->errors());
        }

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=file.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $from = explode(" - ", $request->input('date-range'))[0];
        $to   = explode(" - ", $request->input('date-range'))[1];

        //第一列
        $columns = array("日期", "姓名", "上班時間", "下班時間");

        $all_rows  = $this->getCheckTimeRows($from , $to, $request->input('id'));
        $callback = function() use ($columns, $all_rows)
        {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach($all_rows as $row) {
                if (!empty($row)) {
                    fputcsv($file, $row);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers); 
    }

    private function getCheckTimeRows($from ,$to, $id)
    {
        $mysql =
            "SELECT DATE(c.checkin_at) as date, s.name,
            DATE_FORMAT(c.checkin_at,'%H:%i:%s') as checkin_at,
            DATE_FORMAT(c.checkout_at,'%H:%i:%s') as checkout_at 
            FROM checks c left join  staffs s on s.id = c.staff_id
            WHERE c.staff_id IN (".implode(',', $id).")"
            ." AND checkin_at >= '".$from." 00:00:00'"
            ." AND checkout_at <= '".date('Y-m-d', strtotime('+1 day', strtotime($to)))." 00:00:00'"
            ." AND c.type = 0"
            ." GROUP BY c.staff_id, DATE(c.checkin_at)\n";

        $mysql =
            $mysql." ORDER BY DATE(c.checkin_at)";

        $rows = collect(DB::select($mysql))->map(function ($item) {
            return json_decode(json_encode($item), true);
        })->toArray();
        return $rows;
    }

    public function count_late(Request $request)
    {
        switch ($request->input('action_type')) {

            case 'lookup':
                if ($request->has(['date-range', 'id'])) {
                    $from = explode(" - ", $request->input('date-range'))[0];
                    $to =   explode(" - ", $request->input('date-range'))[1];

                    $rows  = $this->getCountLateRows($from , $to, $request->input('id'));
                }
                break;

            case 'export':
                break;
        }

        $rows = isset($rows) ? $rows : [];

        //form options
        $options['name'] =Staff::all()->pluck('name', 'id')->toArray();

        return view('admin.pages.check.count_late', compact('options', 'rows'));
    }

    private function getCountLateRows($from , $to, $id)
    {
        $mysql =
            "SELECT s.name"
            .", SUM(IF(c.type = ".Check::TYPE_LATE.", 1, 0)) as late_count"
            ." FROM checks c left join  staffs s on s.id = c.staff_id
            WHERE c.staff_id IN (".implode(',', $id).")"
            ." AND checkin_at >= '".$from." 00:00:00'"
            ." AND checkout_at <= '".date('Y-m-d', strtotime('+1 day', strtotime($to)))." 00:00:00'"
            ." GROUP BY c.staff_id"
            ." HAVING late_count > 0"
            ." ORDER BY late_count DESC";

        $rows = collect(DB::select($mysql))->map(function ($item) {
            return json_decode(json_encode($item), true);
        })->toArray();

        return $rows;
    }

    public function annual_stat(Request $request)
    {
        switch ($request->input('action_type')) {

            case 'lookup':
                $rows = Staff::with(['profile', 'get_check_list'])
                    ->whereIn('id', $request->input('id'))
                    ->get()
                    ->map(function ($item, $key) {
                        return $this->getAnnualLeaveStat($item);
                    });
                break;

            case 'export':
                break;
        }
        $rows = isset($rows) ? $rows : [];

        $options['name'] =Staff::all()->pluck('name', 'id')->toArray();

        return view('admin.pages.check.annual_stat', compact('rows', 'options'));
    }

    private function getAnnualLeaveStat($staff)
    {
        $on_board_date = Carbon::createFromFormat('Y-m-d', $staff->profile->on_board_date);
        $on_board_months = $on_board_date->diffInMonths(Carbon::now());

        if ( $on_board_months < 6) {
            $annual_hours = 0;
            $used_hours = $this->getUsedHours($staff, 0);
        }
        elseif ( 6 <= $on_board_months && $on_board_months < 12) {
            $annual_hours = 24;
            $used_hours = $this->getUsedHours($staff, 6);
        }
        elseif ( 12 <= $on_board_months && $on_board_months < 24) {
            $annual_hours = 56;
            $used_hours = $this->getUsedHours($staff, 12);
        }
        elseif ( 24 <= $on_board_months && $on_board_months < 36) {
            $annual_hours = 80;
            $used_hours = $this->getUsedHours($staff, 24);
        }
        elseif ( 36 <= $on_board_months && $on_board_months < 60) {
            $annual_hours = 112;
            $which_year = floor(($on_board_months - 36) / 12);
            $used_hours = $this->getUsedHours($staff, 36 + $which_year * 12);
        }
        elseif ( 60 <= $on_board_months && $on_board_months < 120) {
            $annual_hours = 120;
            $which_year = floor(($on_board_months - 60) / 12);
            $used_hours = $this->getUsedHours($staff, 60 + $which_year * 12);
        }
        elseif ( 120 <= $on_board_months) {
            $annual_hours = 128 + (floor($on_board_months / 12) - 10) * 8;
            if ($annual_hours >= 240) {
                $annual_hours = 240;
            }
            $which_year = floor(($on_board_months - 120) / 12);
            $used_hours = $this->getUsedHours($staff, 120 + $which_year * 12);
        }

        $remained_hours = $annual_hours - $used_hours > 0 ? $annual_hours - $used_hours : 0;

        $row['name'] = $staff->name;
        $row['usable'] = $annual_hours;
        $row['used'] = $used_hours;
        $row['remained'] = $remained_hours;

        return $row;
    }

    private function getUsedHours($staff, $months)
    {
        $on_board_date = Carbon::createFromFormat('Y-m-d', $staff->profile->on_board_date);

        $checks = $staff->get_check_list
            ->where('type', Check::TYPE_ANNUAL_LEAVE)
            ->where('checkin_at', ">=", $on_board_date->addMonths($months));

        return LeaveHelper::countHours($checks);
    }
}
