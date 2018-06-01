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
        1  => "事假",
        2  => "特休",
        3  => "公假",
        4  => '病假',
        5  => 'Online',
        6  => '晚到',
        7  => '喪假',
        8  => '產假',
        9  => '陪產假',
        10 => '婚假',
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

                    $rows  = $this->getStatisticRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value']));
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

    private function getStatisticRows($from, $to, $id, $conditions)
    {
        $mysql =
            "SELECT DATE(c.checkin_at) as date, s.name as name"
            .", SUM(IF(c.type = 1, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as personal_leave_time"
            .", SUM(IF(c.type = 2, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as annual_leave_time"
            .", SUM(IF(c.type = 3, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as official_leave_time"
            .", SUM(IF(c.type = 4, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as sick_leave_time"
            .", SUM(IF(c.type = 5, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as online_time"
            .", SUM(IF(c.type = 7, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as mourning_time"
            .", SUM(IF(c.type = 8, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as maternity_time"
            .", SUM(IF(c.type = 9, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as paternity_time"
            .", SUM(IF(c.type = 10, TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as marriage_time";
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
        $rows = array_where($rows, function($value,$key) {
            return array_sum(array_except($value, ['date', 'name'])) != 0;
        });
        return $rows;
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
        $columns = array("日期", "姓名");
        foreach ($request->input('type') as $type) {
            $columns[] = $this->CHECK_TYPE[$type]."時數";
        }

        $all_rows  = $this->getExportStatisticRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value', 'type']));
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

    private function getExportStatisticRows($from, $to, $id, $conditions)
    {
        $mysql =
            "SELECT DATE(c.checkin_at) as date, s.name";

        foreach ($conditions['type'] as $type) {
            $mysql =
                $mysql.", SUM(IF(c.type = ".$type.", TIMESTAMPDIFF(HOUR,checkin_at,checkout_at), 0)) as ".$this->CHECK_ENG_TYPE[$type]."_time";
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

        $rows = DB::select($mysql);
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
            ." ORDER BY late_count DESC";

        $rows = DB::select($mysql);
        return $rows;

    }
}
