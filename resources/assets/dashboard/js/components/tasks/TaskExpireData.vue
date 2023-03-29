<template>
    <div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="نام خانوادگی" v-model="params.name">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <input class="form-control" type="text" placeholder="موبایل" v-model="params.phone">
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="از تاریخ" locale="fa,en" format="YYYY-M-D" v-model="params.startDate"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <date-picker placeholder="تا تاریخ" locale="fa,en" format="YYYY-M-D" v-model="params.endDate"></date-picker>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <select class="custom-select" v-model="params.type">
                            <option value="all">انتخاب نوع تسک</option>
                            <option value="1">پیامک</option>
                            <option value="0">ثبت نام</option>
                            <option value="2">تماس ورودی</option>
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
                    <div v-show="handelArea" class="col-12 col-md-3 mb-3">
                        <v-select v-model="params.area" :options="areas" label="name" dir="rtl"
                                  placeholder="انتخاب منطقه..."></v-select>
                    </div>
                    <div class="col-12 col-md-3 mb-3">
                        <select class="custom-select" v-model="params.service">
                            <option value="all">انتخاب خدمت درخواستی</option>
                            <option v-for="item in services" v-bind:value="item.id" v-text="item.label"></option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3 mb-2">
                        <button @click="Filter" type="button" class="btn btn-outline-primary py-2 px-4">فیلتر کن</button>
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
        <task-modal></task-modal>
    </div>
</template>

<script>
    import Vuetable from 'vuetable-2/src/components/Vuetable'
    import VuetablePagination from 'vuetable-2/src/components/VuetablePagination'
    import VuetablePaginationInfo from 'vuetable-2/src/components/VuetablePaginationInfo'

    export default {
        components: {
            Vuetable,
            VuetablePagination,
            VuetablePaginationInfo,
        },
        data() {
            return {
                loading: true,
                trans_loading: false,
                callcenters: [],
                representations: [],
                provinces: [],
                cities: [],
                areas: [],
                services: [],

                transfer_form: new Form({
                    type: 'default',
                    tartDate: '',
                    endDate: '',
                    startUser: '',
                    endUser: '',
                    count: '',
                    id: '',
                }),

                params: {
                    name: '',
                    phone: '',
                    startDate: '',
                    endDate: '',
                    type: 'all',
                    status: 'all',
                    user: 'all',
                    province: '',
                    city: '',
                    area: '',
                    service: 'all',
                    exel: false,
                    representation: 'all'
                },
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
                        name: 'register_status',
                        title: 'وضعیت ثبت نام',
                        callback: 'RegisterStatusLabel'
                    },
                    {
                        name: 'text',
                        title: 'متن پیام'
                    },
                    {
                        name: 'user',
                        title: 'کالسنتر'
                    },
                    {
                        name: 'representation',
                        title: 'نمایندگی'
                    },
                    {
                        name: 'type',
                        title: 'نوع تسک',
                        callback: 'typeLabel'
                    },
                    {
                        name: 'service',
                        title: 'خدمت درخواستی'
                    },
                    {
                        name: 'status',
                        title: 'وضعیت'
                    },
                    {
                        name: 'date',
                        title: 'تاریخ'
                    },
                    {
                        name: '__component:actions',
                        title: 'اقدام'
                    },
                ],

                css: {
                    tableClass: 'table table-bordered table-striped table-responsive-md',
                },

                url: '/dashboard/tasks-expire',
            }
        },
        methods: {
            typeLabel(value) {
                if (value === 0) {
                    return '<span class="badge badge-primary">ثبت نام</span>'
                } else if (value === 1) {
                    return '<span class="badge badge-warning">پیامک</span>'
                } else {
                    return '<span class="badge badge-success">تماس ورودی</span>'
                }
            },
            RegisterStatusLabel(value) {
                if (value === true) {
                    return 'ثبت شده';
                } else {
                    return 'جدید';
                }
            },

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
                        name: data.data[i].name['name'],
                        register_status: data.data[i].name['register_status'],
                        phone: data.data[i].phone,
                        representation: data.data[i].representation,
                        service: data.data[i].service,
                        text: data.data[i].text,
                        type: data.data[i].type,
                        user: data.data[i].user,
                        date: data.data[i].date,
                        status: data.data[i].status,
                        per_delete: data.data[i].per_delete,
                    })
                }

                return transformed
            },
            Filter() {
                if (this.params.exel){
                    var url = "/dashboard/tasks-expire?" + $.param(this.params);
                    window.open(url , '_blank');
                } else{
                    this.$refs.vuetable.refresh();
                }
            },
            getCities() {
                axios.post('/dashboard/cities', {
                    'id': this.params.province
                }).then(response => {
                    this.cities = response.data.cities;
                }).catch(error => {
                    toastr.error('خطایی رخ داد، مجدد انتخاب نمایید');
                });
            }
        },
        mounted() {
            this.$events.$on('refresh', e => Vue.nextTick(() => this.$refs.vuetable.refresh()));
        },
        created() {
            axios.get('/dashboard/tasks-requires').then(response => {
                this.callcenters = response.data.data.callcenters;
                this.representations = response.data.data.representations;
                this.provinces = response.data.data.provinces;
                this.areas = response.data.data.areas;
                this.services = response.data.data.services;
            }).catch(error => {
                toastr.error('خطایی رخ داد، صفحه را مجدد لود کنید');
            });
        },
        computed: {
            handelArea() {
                if (this.params.city === null) {
                    return false;
                } else {
                    if (this.params.city.id === 117) {
                        return true;
                    } else {
                        return false;
                    }
                }
            },
        },
    }

</script>
