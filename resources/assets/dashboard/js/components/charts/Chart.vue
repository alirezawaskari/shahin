<template>
 <div>
     <div class="card">
         <div class="card-body">
             <h5 class="card-title">فاکتور های 6 ماه پیش</h5>
             <canvas style="width:100%" height="400" ref="factor_month"></canvas>
         </div>
     </div>
     <div class="card">
         <div class="card-body">
             <h5 class="card-title">فاکتور های 7 روز پیش</h5>
             <canvas style="width:100%" height="400" ref="factor_week"></canvas>
         </div>
     </div>
     <div class="card">
         <div class="card-body">
             <h5 class="card-title">کاربران 6 ماه پیش</h5>
             <canvas style="width:100%" height="400" ref="user_month"></canvas>
         </div>
     </div>
     <div class="card">
         <div class="card-body">
             <h5 class="card-title">کاربران 7 روز پیش</h5>
             <canvas style="width:100%" height="400" ref="user_week"></canvas>
         </div>
     </div>
 </div>
</template>

<script>
    export default {
        props : ['label_month' , 'label_week' , 'factor_month' ,'user_month' , 'factor_week' , 'user_week'],

        data(){
          return {
              options: {
                  tooltips: {
                      callbacks: {
                          label: function(t, d) {
                              var xLabel = d.datasets[t.datasetIndex].label;
                              var yLabel = t.yLabel >= 1000 ?t.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' ریال' : '$' + t.yLabel;
                              return xLabel + ': ' + yLabel;
                          }
                      }
                  },
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true,
                              callback: function(value, index, values) {
                                  if(parseInt(value) >= 1000){
                                      return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +  ' ریال';
                                  } else {
                                      return value +' ریال' ;
                                  }
                              }
                          }
                      }]
                  }
              }
          }
        },
        mounted() {
            var ctx = this.$refs.factor_month.getContext("2d");
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.label_month,
                    datasets: [{
                        label: 'پرداخت های موفق',
                        data: this.factor_month.success,
                        backgroundColor: 'rgba(52, 152, 219,0.5)',
                        borderColor: 'rgba(41, 128, 185,1.0)',
                        borderWidth: 1
                    },{
                        label: 'پرداخت های معلق',
                        data: this.factor_month.unsuccess,
                        backgroundColor: 'rgba(231, 76, 60,.5)',
                        borderColor: 'rgba(192, 57, 43,1.0)',
                        borderWidth: 1
                    }]
                },
                options: this.options
            });

            var ctx = this.$refs.factor_week.getContext("2d");
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.label_week,
                    datasets: [{
                        label: 'پرداخت های موفق',
                        data: this.factor_week.success,
                        backgroundColor: 'rgba(52, 152, 219,0.5)',
                        borderColor: 'rgba(41, 128, 185,1.0)',
                        borderWidth: 1
                    },{
                        label: 'پرداخت های معلق',
                        data: this.factor_week.unsuccess,
                        backgroundColor: 'rgba(231, 76, 60,.5)',
                        borderColor: 'rgba(192, 57, 43,1.0)',
                        borderWidth: 1
                    }]
                },
                options: this.options
            });

            var ctx = this.$refs.user_month.getContext("2d");
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.label_month,
                    datasets: [{
                        label: 'کاربران',
                        data: this.user_month,
                        backgroundColor: 'rgba(52, 152, 219,0.3)',
                        borderColor: 'rgba(41, 128, 185,1.0)',
                        borderWidth: 2
                    }]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            label: function(t, d) {
                                var xLabel = d.datasets[t.datasetIndex].label;
                                var yLabel = t.yLabel >= 1000 ?t.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' ریال' : '$' + t.yLabel;
                                return xLabel + ': ' + yLabel;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true,
                                callback: function(value, index, values) {
                                    if(parseInt(value) >= 1000){
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +  ' ریال' ;
                                    } else {
                                        return value +' ریال' ;
                                    }
                                }

                            }

                        }]
                    }
                }
            });

            var ctx = this.$refs.user_week.getContext("2d");
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.label_week,
                    datasets: [{
                        label: 'کاربران',
                        data: this.user_week,
                        backgroundColor: 'rgba(52, 152, 219,0.3)',
                        borderColor: 'rgba(41, 128, 185,1.0)',
                        borderWidth: 2
                    }]
                },
                options: {
                    tooltips: {
                        callbacks: {
                            label: function(t, d) {
                                var xLabel = d.datasets[t.datasetIndex].label;
                                var yLabel = t.yLabel >= 1000 ?t.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' ریال' : '$' + t.yLabel;
                                return xLabel + ': ' + yLabel;
                            }
                        }
                    },
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero:true,
                                callback: function(value, index, values) {
                                    if(parseInt(value) >= 1000){
                                        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") +  ' ریال' ;
                                    } else {
                                        return value +' ریال' ;
                                    }
                                }

                            }

                        }]
                    }
                }
            });
        },
    }
</script>
