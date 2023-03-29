<template>
    <div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="نام" v-model="params.name">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="نام خانوادگی" v-model="params.family">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="موبایل" v-model="params.phone">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="کد ملی" v-model="params.melicode">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" v-model="params.startDate"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" v-model="params.endDate"></date-picker>
                    </div>
                    <div v-show="per_create" class="col-12 col-md-3 mb-2">
                        <select class="custom-select" v-model="params.level">
                            <option value="all">انتخاب نوع کاربر</option>
                            <option value="user">کاربر عادی</option>
                            <option value="admin">کاربر مدیر</option>
                        </select>
                    </div>
                    <div v-show="per_create" class="col-12 col-md-3 mb-2">
                        <select class="custom-select" v-model="params.representation">
                            <option value="all">انتخاب نمایندگی</option>
                            <option v-for="item in representations" v-bind:value="item.id" v-text="item.fa_name"></option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <v-select @input="getCities" v-model="params.province" :options="provinces" label="name" dir="rtl"
                                  placeholder="انتخاب استان..."></v-select>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <v-select v-model="params.city" :options="cities" label="name" dir="rtl"
                                  placeholder="انتخاب شهر..."></v-select>
                    </div>
                    <div v-show="per_create" class="col-12 col-md-3 mb-2">
                        <div class="custom-control custom-switch mt-2">
                            <input type="checkbox" class="custom-control-input" id="customSwitch" v-model="params.exel">
                            <label class="custom-control-label" for="customSwitch">خروجی اکسل</label>
                        </div>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button @click="Filter" type="button" class="btn btn-outline-primary py-2 px-4">فیلتر کن</button>
                    </div>
                </div>
            </div>
        </div>

        <div v-show="per_delete" class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <alert-error v-for="item in manager_form.errors.all()" :key="item[0]" :form="manager_form">{{ item[0] }}</alert-error>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="custom-select" v-model="manager_form.type">
                            <option value="">انتخاب روش عملیات</option>
                            <option value="delete">حذف انتخاب شده ها</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button @click="userManager" :disabled="manager_form.busy" type="button" class="btn btn-outline-primary py-2 px-4">اجرا کن</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card position-relative">
            <div v-if="loading" class="position-absolute ajax-loading d-flex align-items-center flex-column pt-5">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="sr-only">در حال بارگذاری</span>
                </div>
                <span>در حال بارگذاری...</span>
            </div>
            <div class="card-body">
                <vuetable ref="vuetable"
                          :api-url="url"
                          :fields="fields"
                          :css="css"
                          data-path="mydata"
                          pagination-path="pagination"
                          @vuetable:pagination-data="onPaginationData"
                          v-on:vuetable:loading="loading = true"
                          v-on:vuetable:loaded="loading = false"
                          v-on:vuetable:checkbox-toggled="itemSelected"
                          v-on:vuetable:checkbox-toggled-all="itemSelected"
                          :append-params="params"
                >

                </vuetable>
                <vuetable-pagination-info ref="paginationInfo"
                ></vuetable-pagination-info>
                <vuetable-pagination
                    ref="pagination"
                    @vuetable-pagination:change-page="onChangePage"
                ></vuetable-pagination>
            </div>
        </div>
    </div>
</template>

<script>
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'

    export default {
        props: ['per_delete' , 'per_create'],
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
        },
        data() {
            return {
                loading: true,
                callcenters: [],
                representations: [],
                provinces: [],
                cities: [],

                params: {
                    name: '',
                    phone: '',
                    melicode: '',
                    startDate: '',
                    endDate: '',
                    province: '',
                    city: '',
                    exel: false,
                    representation: 'all',
                    level: 'all',
                },

                manager_form: new Form({
                    type: '',
                    id: '',
                }),

                fields: [
                    {
                        name: '__sequence',
                        title: '#',
                        titleClass: 'center aligned',
                        dataClass: 'center aligned'
                    },
                    {
                        name: '__checkbox',   // <----
                        titleClass: 'center aligned',
                        dataClass: 'center aligned'
                    },
                    {
                        name: 'name',
                        title: 'نام'
                    },
                    {
                        name: 'phone',
                        title: 'موبایل'
                    },
                    {
                        name: 'melicode',
                        title: 'کد ملی',
                    },
                    {
                        name: 'token',
                        title: 'کد کاربری'
                    },
                    {
                        name: 'representation',
                        title: 'نمایندگی'
                    },
                    {
                        name: 'date',
                        title: 'تاریخ'
                    },
                    {
                        name: '__component:user-actions',
                        title: 'اقدام'
                    },
                ],

                css: {
                    tableClass: 'table table-bordered table-striped table-responsive-md',
                },

                url: '/dashboard/users',
            }
        },
        methods: {
            onPaginationData(paginationData) {
                this.$refs.pagination.setPaginationData(paginationData);
                this.$refs.paginationInfo.setPaginationData(paginationData);
            },
            onChangePage(page) {
                this.$refs.vuetable.changePage(page);
            },
            transform: function (data) {
                var transformed = {};

                transformed.pagination = {
                    total: data.meta.total,
                    per_page: data.meta.per_page,
                    current_page: data.meta.current_page,
                    last_page: data.meta.last_page,
                    next_page_url: data.links.next,
                    prev_page_url: data.links.prev,
                    from: data.meta.from,
                    to: data.meta.to
                };

                transformed.mydata = [];

                for (var i = 0; i < data.data.length; i++) {
                    transformed.mydata.push({
                        id: data.data[i].id,
                        name: data.data[i].name,
                        phone: data.data[i].phone,
                        melicode: data.data[i].melicode,
                        representation: data.data[i].representation,
                        token: data.data[i].token,
                        date: data.data[i].date,
                        per_delete: data.data[i].per_delete,
                    });
                }

                return transformed
            },
            Delete(data) {
                swal({
                    title: "آیا اطمینان دارید؟",
                    text: "",
                    icon: "warning",
                    buttons: {
                        confirm: 'بله',
                        cancel: 'خیر'
                    },
                    dangerMode: true
                }).then(res => {
                    if (res) {
                        axios.post('/dashboard/user-delete', {
                            'id': data.id,
                        }).then(response => {
                            if (response.data.operator) {
                                this.$refs.vuetable.refresh();
                                swal("با موفقیت حذف شد!", {
                                    icon: "success",
                                    button: "باشه"
                                });
                            }
                        }).catch(error => {
                            swal("خطایی رخ داد!", {
                                icon: "error",
                                button: "باشه"
                            });
                        });
                    }
                });
            },
            Filter() {
                if (this.params.exel){
                    var url = "/dashboard/users?" + $.param(this.params);
                    window.open(url , '_blank');
                } else{
                    this.$refs.vuetable.refresh();
                }
            },
            itemSelected(){
                this.manager_form.id = this.$refs.vuetable.selectedTo;
            },
            getCities() {
                axios.post('/dashboard/cities', {
                    'id': this.params.province
                }).then(response => {
                    this.cities = response.data.cities;
                }).catch(error => {
                    toastr.error('خطایی رخ داد، مجدد انتخاب نمایید');
                });
            },
            userManager(){
                swal({
                    title: "آیا مطمئن هستید؟",
                    text: "",
                    icon: "warning",
                    buttons: {
                        confirm: 'بله',
                        cancel: 'خیر'
                    },
                    dangerMode: true
                }).then(res => {
                    if (res) {
                        this.manager_form.post('/dashboard/user-manager').then(response => {
                            if (response.data.operator) {
                                this.manager_form.type = '';
                                this.Filter();
                                toastr.success('با موفقیت انجام شد!');
                            }
                        }).catch(error => {

                        });
                    }
                });
            },
        },
        mounted() {
            this.$events.$on('refresh', e => Vue.nextTick(() => this.$refs.vuetable.refresh()));
            this.$events.$on('delete', e => this.Delete(e));
        },
        created() {
            axios.get('/dashboard/tasks-requires').then(response => {
                this.callcenters = response.data.data.callcenters;
                this.representations = response.data.data.representations;
                this.provinces = response.data.data.provinces;
            }).catch(error => {
                toastr.error('خطایی رخ داد، صفحه را مجدد لود کنید');
            });
        },
    }

</script>
