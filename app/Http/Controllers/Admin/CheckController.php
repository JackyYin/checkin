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
            3   => "任意一個",
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
        $staffs  = $this->getStaffRows($from , $to, $request->input('id'), $request->only(['has', 'op', 'value']));
        $callback = function() use ($columns, $staffs)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($staffs as $staff) {
                fputcsv($file, $staff);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers); 
    }

    private function getStaffRows($from, $to, $id, $conditions)
    {
        $staff_array = array();

        if ($id == 0) {
            $staffs = Staff::all();
            
            foreach ($staffs as $staff) {
                $staff_array[] = $this->getSingleStaffRow($from, $to, $staff->id, $conditions);
            } 
        }
        else {
            $staff_array[] = $this->getSingleStaffRow($from, $to, $id, $conditions);
        }

        return $staff_array;
    }

    private function getSingleStaffRow($from, $to, $id, $conditions)
    {
        $staff = Staff::find($id);
        $row = array();
        while (strtotime($from) <= strtotime($to)) {
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

            if ($conditions['has']['work_time']) {
                switch ($conditions['op']['work_time']) {

                    case 0:
                        if ($work_time == $conditions['value']['work_time']) {
                            $row[] =  $from;
                            $row[] =  $staff->name;
                            $row[] = $work_time;
                            $row[] = $leave_time;
                        }
                        break;
                    case 1:
                        if ($work_time >= $conditions['value']['work_time']) {
                            $row[] =  $from;
                            $row[] =  $staff->name;
                            $row[] = $work_time;
                            $row[] = $leave_time;
                        }
                        break;
                    case 2:
                        if ($work_time <= $conditions['value']['work_time']) {
                            $row[] =  $from;
                            $row[] =  $staff->name;
                            $row[] = $work_time;
                            $row[] = $leave_time;
                        }
                        break;
                }
            }
            else {
                $row[] =  $from;
                $row[] =  $staff->name;
                $row[] = $work_time;
                $row[] = $leave_time;
            }
            $from = date ("y-m-d", strtotime("+1 day", strtotime($from)));
        }

        return $row;
    }

    private function getFirstRow($from , $to)
    {
        $columns = array();
        $columns[] = "姓名\日期";
        while (strtotime($from) <= strtotime($to)) {
            $columns[] = $from;
            $from = date ("Y-m-d", strtotime("+1 day", strtotime($from)));
        }

        return $columns;
    }

}
