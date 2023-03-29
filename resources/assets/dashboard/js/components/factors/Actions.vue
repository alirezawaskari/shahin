<template>

    <div class="dropdown">
        <a href="#" class="btn btn-dark btn-floating btn-icon btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
        </a>
        <div class="dropdown-menu">
            <button class="dropdown-item pointer" type="button" @click="itemAction('show', rowData, rowIndex)">مشاهده</button>
            <button class="dropdown-item pointer" type="button" @click="itemAction('delete', rowData, rowIndex)">حذف</button>
        </div>
    </div>

</template>

<script>
    export default {
        props: {
            rowData: {
                type: Object,
                required: true
            },
            rowIndex: {
                type: Number
            }
        },
        methods: {
            itemAction(action, data, index) {
                if (action === 'delete') {
                    if (data.per_detail){
                        this.$events.fire('delete' , data);
                    } else{
                        toastr.error('شما مجاز به این کار نیستید!');
                    }
                }else{
                    window.location.href = `/dashboard/factor/${data.resnumber}/edit`;
                }
            },
        }
    }
</script>
