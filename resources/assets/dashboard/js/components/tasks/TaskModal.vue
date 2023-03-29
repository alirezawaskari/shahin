<template>
    <div class="modal fade" id="task-modal" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <p>
                            شماره موبایل:
                            <span v-text="phone"></span>
                            -
                            <span v-text="status"></span>
                            <span v-html="register_status_label"></span>
                        </p>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link show active" id="service-tab" data-toggle="tab" href="#service" role="tab" aria-controls="service" aria-selected="true">عملیات</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link show" id="status-tab" data-toggle="tab" href="#status" role="tab" aria-controls="status" aria-selected="false">وضعیت</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link show" id="transfer-tab" data-toggle="tab" href="#transfer" role="tab" aria-controls="transfer" aria-selected="false">انتقال</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link show" id="detail-tab" data-toggle="tab" href="#detail" role="tab" aria-controls="detail" aria-selected="false">جزئیات</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade p-t-20 active show" id="service" role="tabpanel" aria-labelledby="service-tab">
                            <div v-show="!register_status" class="row">
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
                                <div class="col-12 col-md-6 mb-3">
                                    <label>شماره موبایل</label>
                                    <input class="form-control disabled" type="text" placeholder="شماره موبایل" v-model="register_form.phone" name="phone">
                                </div>
                                <div class="col-12 col-md-6 mb-3">
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
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب خدمات</label>
                                    <v-select multiple v-model="register_form.service" :options="services" dir="rtl"
                                              placeholder="انتخاب خدمات..."></v-select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>اعتبار فاکتور بر حسب روز</label>
                                    <input class="form-control" type="text" placeholder="اعتبار فاکتور بر حسب روز" v-model="register_form.expire" name="expire">
                                </div>
                                <input type="hidden" v-model="register_form.task" name="task">
                                <input @click="registerForm" :disabled="register_form.busy" class="btn btn-success border-0 btn-block" value="ثبت">
                            </div>
                            <div v-show="register_status" class="row">
                                <div class="col-12">
                                    <alert-error v-for="item in service_form.errors.all()" :key="item[0]" :form="service_form">{{ item[0] }}</alert-error>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب خدمات</label>
                                    <v-select multiple v-model="service_form.service" :options="services" dir="rtl"
                                              placeholder="انتخاب خدمات..."></v-select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>اعتبار فاکتور بر حسب روز</label>
                                    <input class="form-control" type="text" placeholder="اعتبار فاکتور بر حسب روز" v-model="service_form.expire" name="expire">
                                </div>
                                <input type="hidden" v-model="service_form.task" name="task">
                                <input @click="serviceForm" :disabled="service_form.busy" class="btn btn-success border-0 btn-block" value="ثبت">
                            </div>
                        </div>
                        <div class="tab-pane fade p-t-20" id="status" role="tabpanel" aria-labelledby="status-tab">
                            <div class="row">
                                <div class="col-12">
                                    <alert-error v-for="item in status_form.errors.all()" :key="item[0]" :form="status_form">{{ item[0] }}</alert-error>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب دلیل</label>
                                    <select class="form-control" v-model="status_form.reason" name="reason">
                                        <option value="">انتخاب دلیل</option>
                                        <option v-for="item in reason" v-bind:value="item.id">{{ item.title }}</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب تاریخ یادآوری</label>
                                    <date-picker placeholder="انتخاب تاریخ" locale="fa,en" format="YYYY-M-D" v-model="status_form.date" name="date"></date-picker>
                                </div>
                                <div class="col-12 mb-3">
                                    <textarea class="form-control" cols="30" rows="5" placeholder="توضیح مختصر" v-model="status_form.description" name="description"></textarea>
                                </div>
                                <input type="hidden" v-model="status_form.task" name="task">
                                <input @click="statusForm" :disabled="status_form.busy" class="btn btn-success border-0 btn-block" value="ثبت">
                            </div>
                        </div>
                        <div class="tab-pane fade p-t-20" id="transfer" role="tabpanel" aria-labelledby="transfer-tab">
                            <div class="row">
                                <div class="col-12">
                                    <alert-error v-for="item in transfer_form.errors.all()" :key="item[0]" :form="transfer_form">{{ item[0] }}</alert-error>
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label>انتخاب همکار</label>
                                    <select class="form-control" v-model="transfer_form.callcenter" name="callcenter">
                                        <option value="">انتخاب همکار</option>
                                        <option v-for="item in callcenters" v-bind:value="item.id">{{ item.name + ' ' + item.family}}</option>
                                    </select>
                                </div>
                                <input type="hidden" v-model="transfer_form.task" name="task">
                                <input @click="transferForm" :disabled="transfer_form.busy" class="btn btn-success border-0 btn-block" value="ثبت">
                            </div>
                        </div>
                        <div class="tab-pane fade p-t-20" id="detail" role="tabpanel" aria-labelledby="detail-tab">
                            <p class="mb-3 text-dark">فاکتورها</p>
                            <table class="table table-bordered table-striped table-responsive-md mb-5">
                                <thead>
                                <tr>
                                    <th scope="col">شماره</th>
                                    <th scope="col">مبلغ</th>
                                    <th scope="col">نوع</th>
                                    <th scope="col">نمایندگی</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">تاریخ</th>
                                    <th scope="col">آیتم ها</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="item in factors">
                                    <th>{{ item.resnumber }}</th>
                                    <td>{{ item.price }}</td>
                                    <td>{{ item.type }}</td>
                                    <td>{{ item.representation }}</td>
                                    <td>{{ item.status }}</td>
                                    <td>{{ item.date }}</td>
                                    <td>{{ item.items }}</td>
                                </tr>
                                </tbody>
                            </table>
                            <p class="mb-3 text-dark">تاریخچه</p>
                            <table class="table table-bordered table-striped table-responsive-md mb-5">
                                <thead>
                                <tr>
                                    <th scope="col">آپدیت کننده</th>
                                    <th scope="col">کالسنتر</th>
                                    <th scope="col">وضعیت</th>
                                    <th scope="col">تاریخ</th>
                                    <th scope="col">توضیحات</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="item in histories">
                                    <th>{{ item.user }}</th>
                                    <td>{{ item.callcenter }}</td>
                                    <td>{{ item.status }}</td>
                                    <td>{{ item.date }}</td>
                                    <td>{{ item.description }}</td>
                                </tr>
                                </tbody>
                            </table>
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
                id: null,
                phone: null,
                status: null,
                register_status: null,
                callcenters: [],
                services: [],
                reason: [],
                factors: [],
                histories: [],
                provinces: [],
                cities: [],
                areas: [],

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
                    task: '',
                }),
                service_form: new Form({
                    service: [],
                    expire: 3,
                    task: '',
                }),
                status_form: new Form({
                    reason: '',
                    description: '',
                    date: '',
                    task: '',
                }),
                transfer_form: new Form({
                    callcenter: '',
                    task: '',
                }),
            }
        },
        methods: {
            modal(data) {
                this.id = data.id;
                this.register_form.task = data.id;
                this.service_form.task = data.id;
                this.status_form.task = data.id;
                this.transfer_form.task = data.id;
                this.phone = data.phone;
                this.register_form.phone = data.phone;
                this.status = data.status;
                this.register_status = data.register_status;
                $('#task-modal').modal('show');
                axios.post('/dashboard/task-detail', {
                    'id': this.id,
                }).then(response => {
                    this.factors = [];
                    this.histories = [];
                    this.factors = response.data.data.factors;
                    this.histories = response.data.data.histories;
                }).catch(error => {
                    toastr.error('خطایی رخ داد، صفحه را مجدد لود کنید');
                });
            },
            closeModal() {
                $('#task-modal').modal('hide');
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
                this.service_form.post('/dashboard/service-form')
                    .then(({data}) => {
                        if (data.operator) {
                            this.$events.fire('refresh');
                            this.closeModal();
                            this.service_form.service = [];
                            toastr.success('با موفقیت ثبت شد');
                        }
                    });
            },
            statusForm() {
                this.status_form.post('/dashboard/status-form')
                    .then(({data}) => {
                        if (data.operator) {
                            this.$events.fire('refresh');
                            this.closeModal();
                            this.status_form.reason = '';
                            this.status_form.date = '';
                            this.status_form.description = '';
                            toastr.success('با موفقیت ثبت شد');
                        }
                    });
            },
            transferForm() {
                this.transfer_form.post('/dashboard/transfer-form')
                    .then(({data}) => {
                        if (data.operator) {
                            this.$events.fire('refresh');
                            this.closeModal();
                            this.transfer_form.callcenter = '';
                            toastr.success('با موفقیت انتقال یافت');
                        }
                    })
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
                this.reason = response.data.data.reason;
                this.callcenters = response.data.data.callcenters;
                this.services = response.data.data.services;
                this.provinces = response.data.data.provinces;
                this.cities = response.data.data.cities;
                this.areas = response.data.data.areas;
            }).catch(error => {
                toastr.error('خطایی رخ داد، صفحه را مجدد لود کنید');
            });
        },
        filters: {
            numberFormat(value) {
                return String(value).replace(/(.)(?=(\d{3})+$)/g, '$1,');
            }
        },
        computed: {
            register_status_label() {
                if (this.register_status) {
                    return '<span class="badge badge-danger py-2 px-3">کاربر قدیمی</span>'
                } else {
                    return ' <span class="badge badge-success py-2 px-3">کاربر جدید</span>'
                }
            },
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
        mounted() {
            this.$events.$on('showModal', eventData => this.modal(eventData));
        },
    }
</script>
