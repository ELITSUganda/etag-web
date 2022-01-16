<?php 
    use App\Models\District;
    use App\Models\Animal;
    $types = Array(
                'Live' ,
                'Sick' ,
                'Healed',
                'Vaccinated' ,
                'Gave birth' ,
                'Sold',
                'Died',
                'Slaugtered',
                'Stolen' ,
            );
    $data = [];
    $label = [];
    foreach ($types as $key => $d) {
        $data[] = Animal::where('status', $d)->count();
        $label[] = $d;
    } 

?><canvas id="myChartStatus" style="width: 100%;"></canvas>
<script>
$(function () {
    var ctx = document.getElementById("myChartStatus").getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($types); ?>,
            datasets: [{
                label: '# of Votes',
                data: <?= json_encode($data); ?>,
                backgroundColor: [
                    'rgba(255, 99, 132)',
                    'rgba(54, 162, 235)',
                    'rgba(255, 206, 86)',
                    'rgba(75, 192, 192)',
                    'rgba(153, 102, 255)',
                    'rgba(255, 159, 64)'
                ],
                borderColor: [
                    'rgba(255,99,132,1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });
});
</script>