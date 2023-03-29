<template>
    <div>
        <canvas style="width:100%" height="300" ref="paymentpie"></canvas>
    </div>
</template>

<script>

    export default {
        props : ['labels' , 'values'],

        mounted() {
            var ctx = this.$refs.paymentpie.getContext("2d");
            new Chart(ctx, {
                type: 'pie',
                    data: {
                        labels: this.labels,
                        datasets: [{
                            backgroundColor: ["#0bb300","#e21818"],
                            data: this.values,
                        }],
                    },
                options: {
                    title: {
                        display: true,
                        text: 'کل پرداخت ها',
                        fontStyle: 'bold',
                        fontSize: 16
                    },
                    tooltips: {
                        callbacks: {
                            // this callback is used to create the tooltip label
                            label: function(tooltipItem, data) {
                                // get the data label and data value to display
                                // convert the data value to local string so it uses a comma seperated number
                                var dataLabel = data.labels[tooltipItem.index];
                                var yLabel = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index] >= 1000 ?data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index].toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",") + ' ریال' : '$' + data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                                return dataLabel + ': ' + yLabel;

                            }
                        }
                    }
                }
            });
        }
    }
</script>
