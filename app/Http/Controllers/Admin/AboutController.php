<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\MediaLibraryHelper;
use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * The "درباره ما" page each business manages for itself.
 *
 * One `abouts` row per owner (a business, a representation, or the main lalecard site —
 * see About::scopeFilter), edited as one form of named sections that the sibling API
 * serves to the about-us frontend. The five numbered content boxes + four box images of
 * the old lalecard site are gone; `title` / `content` / `content2` stay because the legacy
 * site.about view still renders them.
 */
class AboutController extends Controller
{
    /** Numeric inputs that may arrive with Persian/Arabic digits. */
    private const NUMERIC_FIELDS = ['established_year', 'years_experience', 'satisfied_customers', 'average_rating'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // filter() = a ghorfe user sees only its own business's row, a representation admin
        // only its representation's, a لاله superadmin every row, everyone else the main
        // site's. There is no create action: a "درباره ما" row is not something an admin
        // makes, it is the page an owner already has (see the route's ->except()).
        $abouts = About::filter()->with('aboutable')->orderBy('id')->get();

        return view('Admin.about.list', ['abouts' => $abouts]);
    }

    /**
     * @param $id
     */
    public function show($id)
    {
        //
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit($id)
    {
        // Same filter() as index(): a ghorfe/representation admin must not reach another
        // tenant's row by typing its id into the url.
        $about = $id ? About::filter()->with('aboutable')->find($id) : null;

        if (! $about) {
            return redirect('/dashboard/about');
        }

        $ghorfe = $about->ghorfeOnline;

        // Branches / specialists hang off the BUSINESS, so those two sections exist only
        // for a business-owned page — a representation or main-site page has neither.
        //
        // Only the business's OWN branches, in pivot order: the branch tab manages the list
        // it already has (reorder / detach / edit / create) instead of offering every center
        // in the panel, which let one business put another's branch on its page.
        $branches = $ghorfe ? $ghorfe->centers : collect();

        // Staff of this business's own branches, not every employee in the panel: an
        // employee belongs to the page exactly while they work at one of its branches.
        $employees = $ghorfe
            ? $ghorfe->staff()
                ->with(['user:id,name,family', 'center:id,title'])
                ->whereHas('user')
                ->orderBy('id')
                ->get()
            : collect();

        return view('Admin.about.edit', [
            'about'     => $about,
            'branches'  => $branches,
            'employees' => $employees,
            'faqs'      => $about->faqsQuery()->get(),
        ]);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Scoped like edit() — otherwise the ownership check there is only cosmetic, since
        // update() is reachable on its own.
        $about = About::filter()->find($id);

        if (! $about) {
            return redirect('/dashboard/about');
        }

        // Digits typed on a Persian keyboard would fail `integer`/`numeric` — normalize
        // before validating rather than rejecting input the admin sees as a valid number.
        $request->merge(collect(self::NUMERIC_FIELDS)
            ->filter(fn ($field) => filled($request->input($field)))
            ->mapWithKeys(fn ($field) => [$field => Helper::convert($request->input($field))])
            ->all());

        $request->validate([
            // hero
            'intro_text'            => 'nullable|string|max:5000',
            // business information
            'established_year'      => 'nullable|digits_between:2,4',
            'contact_phone'         => 'nullable|string|max:30',
            'address'               => 'nullable|string|max:1000',
            'working_days'          => 'nullable|string|max:255',
            'working_hours'         => 'nullable|string|max:255',
            // virtual tour (whole section optional — an online business has no venue)
            'tour_title'            => 'nullable|string|max:255',
            'tour_video_link'       => 'nullable|string|max:1000',
            'tour_description'      => 'nullable|string|max:' . About::TOUR_DESCRIPTION_LIMIT,
            // the temp endpoint refuses video mimes, so the file rides the form itself
            'tour_video_file'       => 'nullable|file|mimetypes:video/mp4,video/webm,video/ogg|max:20480',
            // section headings
            'branches_title'        => 'nullable|string|max:255',
            'staff_title'           => 'nullable|string|max:255',
            // "چرا ...؟" stats
            'why_title'             => 'nullable|string|max:255',
            'years_experience'      => 'nullable|integer|min:0|max:200',
            'satisfied_customers'   => 'nullable|integer|min:0|max:4294967295',
            'average_rating'        => 'nullable|numeric|min:0|max:5',
            'stat_experience_label' => 'nullable|string|max:100',
            'stat_customers_label'  => 'nullable|string|max:100',
            'stat_rating_label'     => 'nullable|string|max:100',
            // goals / vision
            'goals_title'           => 'nullable|string|max:255',
            'goals_text'            => 'nullable|string|max:5000',
            // legacy site.about page
            'title'                 => 'nullable|string|max:255',
            'content'               => 'nullable|string',
            'content2'              => 'nullable|string',
            // relations
            'branch_ids'            => 'nullable|array',
            'branch_ids.*'          => 'integer|exists:centers,id',
            'staff_ids'             => 'nullable|array',
            'staff_ids.*'           => 'integer|exists:employees,id',
            // faq repeater
            'faq_ids'               => 'nullable|array',
            'faq_titles'            => 'nullable|array',
            'faq_titles.*'          => 'nullable|string|max:1000',
            'faq_texts'             => 'nullable|array',
            'faq_texts.*'           => 'nullable|string|max:5000',
        ], [
            'established_year.digits_between' => 'سال تاسیس را به صورت عدد وارد کنید (مثلا 1398)',
            'tour_description.max'            => 'متن تور مجازی حداکثر ' . About::TOUR_DESCRIPTION_LIMIT . ' کاراکتر است',
            'tour_video_file.mimetypes'       => 'فرمت ویدیو باید mp4، webm یا ogg باشد',
            'tour_video_file.max'             => 'حجم ویدیو نباید بیشتر از ۲۰ مگابایت باشد',
            'average_rating.max'              => 'میانگین امتیاز باید بین ۰ تا ۵ باشد',
            'years_experience.integer'        => 'سابقه فعالیت را به صورت عدد وارد کنید',
            'satisfied_customers.integer'     => 'تعداد مشتریان راضی را به صورت عدد وارد کنید',
        ]);

        $about->update($request->all());

        // Page images: the blue ajax widget put a TEMP media id in the hidden {field}_path,
        // attachTo moves it into the collection (single-file, so it replaces the old one).
        foreach (About::PAGE_IMAGES as $collection) {
            if ($request->input($collection . '_path')) {
                MediaLibraryHelper::attachTo($about, $request->input($collection . '_path'), $collection);
            }
        }

        $this->syncTourVideo($about, $request);
        $this->syncBusinessRelations($about, $request);
        $this->syncFaqs($about, $request);

        // Public about pages are cached for an hour, so forget the affected owner key.
        Cache::forget('about');
        if ($about->representation) {
            Cache::forget('about:representation:' . $about->representation->id);
        }

        // Back to the same tab of the same page: this form is long and an admin editing
        // the stats does not want to be dropped on the list and have to navigate back.
        return redirect()->route('about.edit', $about->id)->with([
            'update'     => 'success',
            'active_tab' => $request->input('active_tab', 'identity_info'),
        ]);
    }

    /**
     * Virtual-tour video: attach a newly uploaded file, or drop the stored one.
     *
     * This is the one media field NOT on the shared ajax pipeline —
     * uploadFilesTemporarily whitelists images and documents only, so a video has to be
     * posted with the form and attached straight from the request. attachUpload's image
     * optimizer leaves non-raster extensions untouched, so the file is stored verbatim.
     */
    private function syncTourVideo(About $about, Request $request): void
    {
        if ($request->hasFile('tour_video_file')) {
            MediaLibraryHelper::attachUpload($about, $request->file('tour_video_file'), 'tour_video');
            return;
        }

        if ($request->boolean('remove_tour_video')) {
            foreach ($about->getMedia('tour_video') as $media) {
                MediaLibraryHelper::deleteMedia($media);
            }
            $about->unsetRelation('media');
        }
    }

    /**
     * Branch list + branch staff.
     *
     * Both hang off the BUSINESS, not off the about row, because the branch and partner
     * frontends read the same links. A representation / main-site page has no business and
     * therefore neither section — nothing to sync.
     */
    private function syncBusinessRelations(About $about, Request $request): void
    {
        $ghorfe = $about->ghorfeOnline;

        if (! $ghorfe) {
            return;
        }

        // Row order = card order on the page, so the pivot keeps it as `sort`.
        //
        // sync() (not syncWithoutDetaching): the posted list IS the list, so a row the admin
        // removed from the table has to be detached. The tab always submits the field, so an
        // empty array genuinely means "no branches" rather than "section not rendered".
        $branchIds = collect($request->input('branch_ids', []))
            ->values()
            ->mapWithKeys(fn ($id, $index) => [(int) $id => ['sort' => $index]])
            ->all();

        $ghorfe->centers()->sync($branchIds);

        // Staff are not a list of their own: an employee is on the page while they work at
        // one of the branches. Removing their row therefore cuts that link — and only for
        // employees of THIS business's branches, so a save can never touch anyone else's.
        $keep = collect($request->input('staff_ids', []))->map(fn ($id) => (int) $id)->all();

        $ghorfe->staff()->whereNotIn('id', $keep)->update(['center_id' => null]);
    }

    /**
     * The FAQ repeater: create / update / delete in one pass.
     *
     * `faqs` already existed and is keyed by ghorfe_online_list_id — null for the main
     * site, which is exactly what a main-site about row carries, so one scope covers both.
     * A representation page has no key of its own and would collide with the main site's
     * null, so it gets no FAQ section (the view hides it).
     */
    private function syncFaqs(About $about, Request $request): void
    {
        if ($about->representation) {
            return;
        }

        $ids    = $request->input('faq_ids', []);
        $titles = $request->input('faq_titles', []);
        $texts  = $request->input('faq_texts', []);

        $kept = [];

        foreach ($titles as $index => $title) {
            $title = trim((string) $title);

            // A blank question is an empty repeater row, not a deletion request — and an
            // empty row must never become an empty FAQ.
            if ($title === '') {
                continue;
            }

            $attributes = [
                'title' => $title,
                'text'  => trim((string) ($texts[$index] ?? '')),
            ];

            $id = $ids[$index] ?? null;

            // Re-fetch through the owner scope: the posted id is user input and must not be
            // able to point at another business's question.
            $faq = $id ? $about->faqsQuery()->find($id) : null;

            if ($faq) {
                $faq->update($attributes);
            } else {
                $faq = $about->faqsQuery()->create($attributes + [
                    'ghorfe_online_list_id' => $about->ghorfeOnline?->id,
                ]);
            }

            $kept[] = $faq->id;
        }

        // Rows the admin removed from the repeater (and rows they blanked out).
        $about->faqsQuery()->whereNotIn('id', $kept ?: [0])->delete();
    }

    /**
     * @param $id
     */
    public function destroy($id)
    {
        //
    }

}
