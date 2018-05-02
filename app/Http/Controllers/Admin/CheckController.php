<?php

namespace App\Http\Controllers\Admin;

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
            '==' => 0,
            '>=' => 1,
            '<=' => 2
    ];

    public function export_page()
    {
        $options['name'] = Staff::all()->pluck('name', 'id')->toArray();
        array_unshift($options['name'], '所有人');
        $options['operators'] = array([
            0   => "等於",
            1   => "大於等於",
            2   => "小於等於",
        ]);
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

        $columns = array("日期", "姓名", "工作時數", "請假時數");
        $all_rows  = $this->getDataRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value']));
        $callback = function() use ($columns, $all_rows)
        {
            $file = fopen('php://output', 'w');
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
        $data = array();

        while (strtotime($from) <= strtotime($to)) {

            if ($id == 0) {
                $staffs = Staff::all();

                foreach ($staffs as $staff) {
                    $data[] = $this->getSingleStaffRow($from, $staff->id, $conditions);
                }
            }
            else {
                $data[] = $this->getSingleStaffRow($from, $id, $conditions);
            }
            $from = date ("y-m-d", strtotime("+1 day", strtotime($from)));
        }
        return $data;
    }

    private function getSingleStaffRow($from, $id, $conditions)
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

    private function parseOperator($value1, $operator, $value2)
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
