@extends('site.master')

@section('title')
    درباره ما
@endsection

@section('content')

    <div class="about" style="margin-top: 100px">
        {{--banner--}}
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <b style="font-size: 20pt">
                        شرکت خدمات رفاهی لاله
                    </b>
                </div>
{{--                <div class="col-md-12">--}}
{{--                    <video width="100%"  controls>--}}
{{--                        <source src="https://lalecard.ir/upload/ads_videos/company.mp4" type="video/mp4">--}}
{{--                    </video>--}}
{{--                </div>--}}
            </div>
        </div>
        <div class="banner position-relative bg-white text-dark">
            <div class="overlay position-absolute"></div>
            <div class="container py-4">
                <h2 class="text-orange pt-5 position-relative z-index-5 text-center">{{ $about->title }}</h2>
                <div class="row">
                    <div class="col-12 py-5">
                        {!! $about->content !!}
                    </div>
                </div>
            </div>
        </div>
        {{--section--}}
        <div class="section py-4">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-5 py-5 my-auto mr-auto">
                        {!! $about->content2 !!}
                    </div>
                    <div class="d-none d-md-block col-md-6 pl-0 text-left">
                        <img src="{{asset('/assets/site/images//about-left-img.png')}}" class="img-fluid" alt="">
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                   <div class="col-md-4">
                       <div >
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/WhatsApp Image 2021-08-09 at 3.55.17 PM.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/WhatsApp Image 2021-08-10 at 10.09.34 AM.jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/WhatsApp Image 2021-08-10 at 10.09.34 AM (1).jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/WhatsApp Image 2021-08-10 at 10.09.34 AM (2).png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/New Project.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/435815017_413873.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/463506811_297466.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/464138619_301767.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/464019774_327626.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2019/10/images/447819368_524596.png" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2022/6/WhatsApp Image 2022-06-27 at 2.14.30 PM (1).jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2022/6/WhatsApp Image 2022-06-27 at 2.14.30 PM (2).jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2022/6/WhatsApp Image 2022-06-27 at 2.14.31 PM.jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                           <a class="lightbox" href="#dog">
                               <img src="http://lalecard.ir/upload/summernote/2022/6/WhatsApp Image 2022-06-27 at 2.14.31 PM (1).jpeg" onclick="linkImage(this.src)" width="100%">
                           </a>
                       </div>
                   </div>
                </div>
            </div>
        </div>
    </div>
@endsection
