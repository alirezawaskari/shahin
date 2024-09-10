<?php

namespace App\Http\Controllers\Admin;

use App\Models\Callcenter;
use App\Models\Province;
use App\Models\Resume;
use App\Models\Card;
use App\Models\RecruitmentNotice;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\ResumeCollection;
use App\Http\Controllers\Controller;
use App\Exports\ResumsExport;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ResumeController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function index()
    {
        $delete = false;

        if (auth()->user()->hasPermission('resume'))
        {
            $provinces =Province::all();
            if (auth()->user()->isAbleTo('resume-delete')){
                $delete = true;
            }

            return view('Admin.resume.all' , compact('delete','provinces'));
        }
        return abort(401);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function desktop()
    {
        $user = auth()->user();
        if ($user->isAbleTo('resume-desktop')){
            return view( 'Admin.resume.desktop' );
        }
        return abort(401);
    }

    /**
     * @param Resume $resume
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function show(Resume $resume)
    {
        return view('Admin.resume.show',compact('resume'));
    }

    /**
     * @param Request $request
     * @return ResumeCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filter(Request $request)
    {
        $resumes = Resume::filter()->latest();
        if(auth()->user()->admin_representation_id)
        {
            $resumes = auth()->user()->admin_representation->resumes()->filter()->latest();
        }
        if ($request->excel == 'on') {
            return (new ResumsExport($resumes))->download('resumes.xlsx');
        }
        return new ResumeCollection($resumes->paginate(20));
    }

    /**
     * @param Request $request
     * @return ResumeCollection|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function filterDesktop(Request $request)
    {
        $resumes = Resume::filter()->where('card_id', auth()->user()->roles->first()->card_id)->where('status' , '!=' , 3)->where('status' , '!=' , 4)->latest();
        if(auth()->user()->admin_representation_id)
        {
            $resumes = auth()->user()->admin_representation->resumes()->filter()->where('card_id', auth()->user()->roles->first()->card_id)->where('status' , '!=' , 3)->where('status' , '!=' , 4)->latest();
        }
        if ($request->excel == 'on') {
            return (new ResumsExport($resumes))->download('resumes.xlsx');
        }
        return new ResumeCollection($resumes->paginate(20));
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|void
     */
    public function create()
    {
        if (auth()->user()->hasPermission('resume'))
        {
            $cards = Card::all();
            $recruitment_notices = RecruitmentNotice::whereStatus(true)->get();

            return view('Admin.resume.create' , compact('recruitment_notices' , 'cards'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $this->validate(request(), [
            'name' => 'required|string',
            'family' => 'required|string',
            'phone' => ['required', 'digits:11'],
            'email' => 'nullable|email',
            'text' => 'nullable|string',
			'recruitment_notice_id' => 'required',
        ]);
        $inputs = $request->all();
		$recruitment_notice = RecruitmentNotice::find($inputs['recruitment_notice_id']);

		if (is_null($recruitment_notice)) {
            $status = 404;
            return redirect()->back()->with([
              'resume_success' => 'error'
            ]);
		}
        //check if user not exist -> create user
        User::createUser($request);
        $user = User::where('phone', $request->phone)->first();

        if ($request->status==0 || $request->status==5 ||$request->status==1 ){
            //create task to follow up
            $callcenter_roles = User::where('level', 'admin')->where('active', 1)->whereHas('roles', function ($query) {
                $query->where('name', 'superadmin');
            })->get();
            $description = "جویای کار،
            عنوان شغلی:
           {$recruitment_notice->title}";
            Callcenter::createTask($user, $description , $callcenter_roles);
        }
        $inputs['resume'] = $request->urlresume;
		unset($inputs['urlresume']);
        $inputs['name']=$request->name.' '.$request->family;
        unset($inputs['family']);
        $resume = Resume::create($inputs);

        if ($request->urlresume) {
            $resume->update(['resume' => Helper::moveImage($request->urlresume,'document')]);
        }
        if(auth()->user()->admin_representation_id)
        {
            $resume->update(['representation_id' => auth()->user()->admin_representation_id]);
        }
        return redirect(route('resume.index'))->with([
            'create'=>'success'
        ]);
    }

    /**
     * @param Resume $resume
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\never
     */
    public function edit(Resume $resume)
    {
        if (auth()->user()->hasPermission('resume'))
        {
            $cards = Card::all();
			$recruitment_notices = RecruitmentNotice::whereStatus(true)->get();
            $user = User::where('phone', $resume->phone)->first();
            $comments = Helper::userHistories($user);

            return view('Admin.resume.edit' , compact('user' , 'resume' , 'cards' , 'recruitment_notices' , 'comments'));
        }
        return abort(401);
    }

    /**
     * @param Request $request
     * @param Resume $resume
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Foundation\Application|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, Resume $resume)
    {
        $inputs = $request->all();
        if($request->description)
        {
            $resume->histories()->create([
                'user_id' => auth()->user()->id,
                'status' => 2,
                'description' => $request->description,
            ]);
        }
        unset($inputs['resume']);
        if ($request->urlresume) {
            $inputs['resume'] = Helper::moveImage($request->urlresume,'document');
        }
		unset($inputs['urlresume']);

        $resume->update($inputs);

        return redirect(route('resume.index'))->with([
            'update'=>'success'
        ]);
    }


    /**
     * delete resume
     * @param Resume $resume
     * @return Resume
     */
    public function destroy(Resume $resume)
    {
        if (auth()->user()->hasPermission('resume-delete'))
        {
           $resume->delete();
            return response(['status'=>'success']);
        }
            return response(['status'=>'error']);

    }
    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function transfer(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'id' => 'required'
        ]);
        if ($validation->fails()){
            return response(['status'=>'error','messages'=>$validation->messages()->all()]);
        }
        $resumes = Resume::whereIn('id' , $request->id);
        $resumes->update(['representation_id' => $request->end_representation]);
        return response([
            'operator' => true,
            'status'=>'success',
            'messages'=>'انتقال با موفقیت انجام گرفت .'
        ]);
    }
}
