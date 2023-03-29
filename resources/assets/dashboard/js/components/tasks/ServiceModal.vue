<template>
    <div class="modal fade" id="service-modal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <span>ثبت خدمات سریع</span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="alert alert-warning alert-with-border" role="alert">
                                <h4 class="alert-heading">کاربر قدیمی</h4>
                                <p>از طریق فرم زیر میتوانید برای کاربری که قبلا در سیستم ثبت نام است، فاکتور صادر نمایید.</p>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <alert-error v-for="item in service_form.errors.all()" :key="item[0]" :form="service_form">{{ item[0] }}</alert-error>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>انتخاب کاربر</label>
                                    <v-select v-model="service_form.user" label="label" dir="rtl" @search="searchUser" :options="users" :filterable="false"
                                              placeholder="شماره موبایل را جستجو کنید...">
                                    </v-select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>انتخاب خدمات</label>
                                    <v-select multiple v-model="service_form.service" :options="services" dir="rtl"
                                              placeholder="انتخاب خدمات..."></v-select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>اعتبار فاکتور بر حسب روز</label>
                                    <input class="form-control" type="text" placeholder="اعتبار فاکتور بر حسب روز" v-model="service_form.expire" name="expire">
                                </div>
                                <input type="hidden" v-model="service_form.task" name="task">
                                <input @click="serviceForm" :disabled="service_form.busy" class="btn btn-warning border-0 btn-block" value="ثبت">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="alert alert-success alert-with-border" role="alert">
                                <h4 class="alert-heading">کاربر جدید</h4>
                                <p>از طریق فرم زیر میتوانید کاربر جدید را در سیستم ثبت نام کنید و در صورت نیاز برای آن فاکتور صادر نمایید.</p>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <alert-error v-for="item in register_form.errors.all()" :key="item[0]" :form="register_form">{{ item[0] }}</alert-error>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>نام</label>
                                    <input class="form-control" type="text" placeholder="نام" name="name" v-model="register_form.name">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>نام خانوادگی</label>
                                    <input class="form-control" type="text" placeholder="نام خانوادگی" v-model="register_form.family" name="family">
                                </div>
                                <div class="col-12 mb-3">
                                    <label>شماره موبایل</label>
                                    <input class="form-control disabled" type="text" placeholder="شماره موبایل" v-model="register_form.phone" name="phone">
                                </div>
                                <div class="col-12 mb-3">
                                    <label>کد ملی</label>
                                    <input class="form-control" type="text" placeholder="کد ملی" v-model="register_form.meli_code" name="meli_code">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب استان</label>
                                    <v-select @input="getCities" v-model="register_form.province" :options="provinces" label="name" dir="rtl"
                                              placeholder="انتخاب استان..."></v-select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب شهر</label>
                                    <v-select v-model="register_form.city" :options="cities" label="name" dir="rtl"
                                              placeholder="انتخاب شهر..."></v-select>
                                </div>
                                <div v-show="handelArea" class="col-12 col-md-6 mb-3">
                                    <label>انتخاب منطقه</label>
                                    <v-select v-model="register_form.area" :options="areas" label="name" dir="rtl"
                                              placeholder="انتخاب منطقه..."></v-select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>انتخاب خدمات</label>
                                    <v-select multiple v-model="register_form.service" :options="services" dir="rtl"
                                              placeholder="انتخاب خدمات..."></v-select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label>اعتبار فاکتور بر حسب روز</label>
                                    <input class="form-control" type="text" placeholder="اعتبار فاکتور بر حسب روز" v-model="register_form.expire" name="expire">
                                </div>
                                <input @click="registerForm" :disabled="register_form.busy" class="btn btn-success border-0 btn-block" value="ثبت">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                services: [],
                provinces: [],
                cities: [],
                areas: [],
                users: [],

                register_form: new Form({
                    name: '',
                    family: '',
                    phone: '',
                    province: '',
                    city: '',
                    area: '',
                    meli_code: '',
                    service: [],
                    expire: 3,
                }),
                service_form: new Form({
                    user: '',
                    service: [],
                    expire: 3,
                }),
            }
        },
        methods: {
            searchUser(search, loading) {
                if (search.length === 11) {
                    loading(true);
                    axios.post('/dashboard/fetch-user', {
                        'phone': search,
                    }).then(response => {
                        this.users = response.data.user;
                        loading(false);
                    }).catch(error => {
                        toastr.error('خطایی رخ داد، مجدد تلاش کنید');
                    });
                }
            },
            closeModal() {
                $('#service-modal').modal('hide');
            },
            registerForm() {
                this.register_form.post('/dashboard/register-form')
                    .then(({data}) => {
                        if (data.operator) {
                            this.$events.fire('refresh');
                            this.closeModal();
                            this.register_form.service = [];
                            this.register_form.name = '';
                            this.register_form.family = '';
                            this.register_form.phone = '';
                            this.register_form.province = '';
                            this.register_form.city = '';
                            this.register_form.area = '';
                            this.register_form.meli_code = '';
                            toastr.success('با موفقیت ثبت شد');
                        }
                    })
            },
            serviceForm() {
                this.service_form.post('/dashboard/service-form-global')
                    .then(({data}) => {
                        if (data.operator) {
                            this.$events.fire('refresh');
                            this.closeModal();
                            this.service_form.service = [];
                            this.users = [];
                            toastr.success('با موفقیت ثبت شد');
                        }
                    });
            },
            getCities() {
                axios.post('/dashboard/cities', {
                    'id': this.register_form.province
                }).then(response => {
                    this.cities = response.data.cities;
                }).catch(error => {
                    toastr.error('خطایی رخ داد، مجدد انتخاب نمایید');
                });
            }
        },
        created() {
            axios.get('/dashboard/tasks-requires').then(response => {
                this.services = response.data.data.services;
                this.provinces = response.data.data.provinces;
                this.cities = response.data.data.cities;
                this.areas = response.data.data.areas;
            }).catch(error => {
                toastr.error('خطایی رخ داد، صفحه را مجدد لود کنید');
            });
        },
        computed: {
            handelArea() {
                if (this.register_form.city === null) {
                    return false;
                } else {
                    if (this.register_form.city.id === 117) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
        },
    }
</script>
