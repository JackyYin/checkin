<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Validator;
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

    private $CHECK_TYPE = [
        1 => "事假",
        2 => "特休",
        3 => "公假",
        4 => '病假',
        5 => 'Online',
    ];

    private $CHECK_ENG_TYPE = [
        1 => "personal_leave",
        2 => "annual_leave",
        3 => "official_leave",
        4 => 'sick_leave',
        5 => 'online',
    ];
    public function export_page()
    {
        $options['name'] =Staff::all()->pluck('name', 'id')->toArray();
        $options['operators'] = array(
            0   => "等於",
            1   => "大於等於",
            2   => "小於等於",
        );
        $options['type'] = array(
            Check::TYPE_PERSONAL_LEAVE => '事假',
            Check::TYPE_ANNUAL_LEAVE   => '特休',
            Check::TYPE_OFFICIAL_LEAVE => '公假',
            Check::TYPE_SICK_LEAVE     => '病假',
            Check::TYPE_ONLINE         => 'Online',
        );

        return view('admin.pages.check.export_page', compact('options'));
    }

    public function export(Request $request)
    {
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
        $columns = array("日期", "姓名");
        foreach ($request->input('type') as $type) {
            $columns[] = $this->CHECK_TYPE[$type]."時數";
        }

        $all_rows  = $this->getDataRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value', 'type']));
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

    private function getDataRows($from, $to, $id, $conditions)
    {
        $mysql =
            "SELECT DATE(c.checkin_at) as date, s.name";

        foreach ($conditions['type'] as $type) {
            $mysql =
                $mysql.", SUM(IF(c.type = ".$type.", TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as ".$this->CHECK_ENG_TYPE[$type];
        }

        $mysql =
            $mysql." FROM checks c left join  staffs s on s.id = c.staff_id
            WHERE c.staff_id IN (".implode(',', $id).")"
            ." AND checkin_at >= '".$from." 00:00:00'"
            ." AND checkout_at <= '".date('Y-m-d', strtotime('+1 day', strtotime($to)))." 00:00:00'"
            ." GROUP BY c.staff_id, DATE(c.checkin_at)\n";

        if (array_key_exists('has', $conditions) && $conditions['has']['work_time']) {
            $mysql =
                $mysql." HAVING SUM(TIMESTAMPDIFF(HOUR,checkin_at,checkout_at)) ".$this->OPERATOR[$conditions['op']['work_time']]." ".$conditions['value']['work_time']."\n".
                " ORDER BY DATE(c.checkin_at)";
        }
        else {
            $mysql =
                $mysql." ORDER BY DATE(c.checkin_at)";
        }

        $rows = DB::select($mysql);
        return $rows;
    }

    private function old_getDataRows($from, $to, $id, $conditions)
    {
        $data = array();

        if ($id == 0) {
            while (strtotime($from) <= strtotime($to)) {
                $staffs = Staff::all();
                foreach ($staffs as $staff) {
                    $data[] = $this->getSingleStaffRow($from, $staff->id, $conditions);
                }
                $from = date ("y-m-d", strtotime("+1 day", strtotime($from)));
            }
        }
        else {
            while (strtotime($from) <= strtotime($to)) {
                $data[] = $this->getSingleStaffRow($from, $id, $conditions);
                $from = date ("y-m-d", strtotime("+1 day", strtotime($from)));
            }
        }
        return $data;
    }

    private function old_getSingleStaffRow($from, $id, $conditions)
    {
        $staff = Staff::find($id);
        $row = array();
        $work_time = 0;
        $leave_time = 0;
        $checks = $staff->range_one_day($from);

        foreach ($checks as $item) {
            switch ($item->type) {

                case Check::TYPE_NORMAL:
                    $work_time += round((strtotime($item->checkout_at) - strtotime($item->checkin_at))/3600, 1);
                    break;
                default:
                    $leave_time += round((strtotime($item->checkout_at) - strtotime($item->checkin_at))/3600, 1);
                    break;
            }
        }

        if ($work_time == 0 && $leave_time == 0) {
            return $row;
        }

        if (array_key_exists('has', $conditions) && $conditions['has']['work_time']) {
            if ($this->parseOperator($work_time, $conditions['op']['work_time'], $conditions['value']['work_time'])) {
                $row[] =  $from;
                $row[] =  $staff->name;
                $row[] = $work_time;
                $row[] = $leave_time;
            }
        }
        else {
            $row[] =  $from;
            $row[] =  $staff->name;
            $row[] = $work_time;
            $row[] = $leave_time;
        }

        return $row;
    }

    private function old_parseOperator($value1, $operator, $value2)
    {
        switch ($operator) {
            case 0:
                return $value1 == $value2;
                break;
            case 1:
                return $value1 >= $value2;
                break;
            case 2:
                return $value1 <= $value2;
                break;
        }
    }
}
