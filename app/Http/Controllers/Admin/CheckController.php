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
    public function export_page()
    {
        $options['name'] = Staff::all()->pluck('name', 'id')->toArray();
        array_unshift($options['name'], 'all');
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
        $columns = $this->getFirstRow($from , $to);
        $staffs  = $this->getStaffRows($from , $to, $request->input('id'));
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

    private function getStaffRows($from, $to, $id)
    {
        $staff_array = array();

        if ($id == 0) {
            $staffs = Staff::all();
            
            foreach ($staffs as $staff) {
                $staff_array[] = $this->getSingleStaffRow($from, $to, $staff->id);
            } 
        }
        else {
            $staff_array[] = $this->getSingleStaffRow($from, $to, $id);
        }

        return $staff_array;
    }

    private function getSingleStaffRow($from, $to, $id)
    {
        $staff = Staff::find($id);
        $row = array();
        $row[] =  $staff->name;
        while (strtotime($from) <= strtotime($to)) {
            $work_time = 0;
            $checks = $staff->range_one_day($from);

            foreach ($checks as $item) {
                switch ($item->type) {
                
                    case 0:
                        $work_time += round((strtotime($item->checkout_at) - strtotime($item->checkin_at))/3600, 1);
                        break;
                    case 1:
                        $work_time -= round((strtotime($item->checkout_at) - strtotime($item->checkin_at))/3600, 1);
                        break;
                }
            }

            $row[] = $work_time;
            $from = date ("y-m-d", strtotime("+1 day", strtotime($from)));
        }

        return $row;
    }
}
