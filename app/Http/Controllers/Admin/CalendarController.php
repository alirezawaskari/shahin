<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Center;
use App\Models\User;
use App\Models\WorkCalendar;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;


class CalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['start_time' => 'required','end_time'=>'required'],
            [
                'start_time.required' => 'لطفا زمان شروع را وارد کنید',
                'end_time.required' => 'لطفا زمان پایان را وارد کنید',
            ]);


        //store calender event
        if($request->start_time < $request->end_time){

            $user_id=$request->user_id;

            //convert  miladi to shamsi
            $persian_start_date = verta($request->start_date)->format('Y-m-d');
            $persian_end_date = verta($request->end_date)->format('Y-m-d');

            if(User::checkManager() != null) $center_id = User::checkManager();
            if (\Auth::user()->isAbleTo('product-detail')) $center_id=$request->center_id;
            if(isset($center_id)){
                $res_calendar=[];

                $begin = new \DateTime($persian_start_date);
                $end = new \DateTime($persian_end_date);

                //find day between to date , and store one record for date
                $interval_one_day = \DateInterval::createFromDateString('1 day');
                $period = new \DatePeriod($begin, $interval_one_day, $end);

                foreach ($period as $dt) { $dates[]=$dt->format("Y-m-d"); }
                if(isset($dates)){
                    foreach ($dates as $date) {
                        $date_en = Helper::vertaToMiladi($date);

                        if(isset($user_id)){
                            $has_work_calendar = WorkCalendar::where('center_id',$center_id)
                                ->where('date',$date)->where('user_id',$user_id)->count();
                        } else {
                            $has_work_calendar = WorkCalendar::where('center_id',$center_id)
                                ->where('date',$date)->count();
                        }
                        if($has_work_calendar == 0){
                            $request->start_time= Helper::convert($request->start_time);
                            $request->end_time= Helper::convert($request->end_time);
                            if(isset($user_id)){
                                $work_calendar = WorkCalendar::create(['center_id'=>$center_id,'date'=>$date
                                    ,'date_en'=>$date_en,'user_id'=>$user_id,
                                    'start_time'=>$request->start_time
                                    ,'end_time'=>$request->end_time,'time_intervals'=>$request->time_intervals]);
                            }else{
                                $work_calendar = WorkCalendar::create(['center_id'=>$center_id,'date'=>$date
                                    ,'date_en'=>$date_en,'start_time'=>$request->start_time
                                    ,'end_time'=>$request->end_time,'time_intervals'=>$request->time_intervals]);
                            }

                            $fullName='';
                            $center_name = Center::find($center_id)->title;
                            if(isset($user_id)) {
                                $fullName = User::find($user_id)->name . ' ' . User::find($user_id)->family;
                            }

                            $res_calendar[]=['id'=>$work_calendar->id,'date_en'=>$work_calendar->date_en,'status'=>'true','center_name'=>$center_name,'fullName'=>$fullName];

                        }
                    }
                    return response($res_calendar);
                }
            }
        }
        return response(['status'=>'301']);
    }


    public function allCalenderEvent(Request $request)
    {
        $center_id=$request->center_id_calendar; $calendar_lists=[];
        if (\Auth::user()->isAbleTo('product-detail') &&  ( isset($request->user_id) && $request->user_id != null) ) {
            $calendar_lists = WorkCalendar::select('id','date_en','center_id','user_id','start_time','end_time')
                ->where('center_id',$center_id)->where('user_id',$request->user_id)->get();
        }
        if(\Auth::user()->isAbleTo('product-detail') && $center_id != null){
            $calendar_lists = WorkCalendar::select('id','date_en','center_id','user_id','start_time','end_time')
                ->where('center_id',$center_id)->get();
        }

        //store manager or center manager
        if(User::checkManager() != null) {
            if ($center_id != null && ( isset($request->user_id) && $request->user_id != null)) {
                $calendar_lists = WorkCalendar::select('id','date_en','center_id','user_id','start_time','end_time')->where('center_id',$center_id)->where('user_id',$request->user_id)->get();
            }
        }

        foreach ($calendar_lists as $calendar_list) {
            $calendar_list['fullName']='';
            $calendar_list['center_name'] = Center::find($calendar_list->center_id)->title;
            if($calendar_list->user_id != '')$calendar_list['fullName'] = User::find($calendar_list->user_id)->name . ' ' .User::find($calendar_list->user_id)->family;
        }
        return $calendar_lists;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($center_id)
    {
        //show full calendar by center id
        $center = Center::where('id',$center_id)->select('id','title')->first();
        if($center){
            return view('Admin.calendar.list',compact('center'));
        }

        return redirect()->back()->with([
            'update' => 'warning'
        ]);

    }


    public function fullCalendarDoctor($user_id)
    {
        $user = User::where('id',$user_id)->select('id','name','family','center_id')->first();
        $center = Center::where('id',$user->center_id)->select('id','title')->first();
        if($center){
            return view('Admin.calendar.list',compact('center','user'));
        }

        return redirect()->back()->with([
            'create' => 'warning'
        ]);
    }



    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if ($id) {
            $calendar= WorkCalendar::find($id);
            $calendar['center_name']=Center::find($calendar->center_id)->title;
            if($calendar->user_id != '')
            $calendar['fullName']=User::find($calendar->user_id)->name . ' ' . User::find($calendar->user_id)->family;
            return view('Admin.calendar.edit',['calendar' => $calendar]);
        }
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($id) {
            $calendar = WorkCalendar::find($id);
            $request['start_time'] = Helper::convert($request->start_time);
            $request['end_time'] = Helper::convert($request->end_time);
            $calendar->update($request->all());
        }
        return redirect()->back()->with([
            'update' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
