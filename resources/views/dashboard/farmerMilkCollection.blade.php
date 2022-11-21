<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3>
            <b>Milk collection - {{ count($labels) }} days ago</b>
        </h3>
        <div>
            <a href="{{ url('/events') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">


        <canvas id="farmerMilkCollection" style="width: 100%;"></canvas>

        <h3>
            <b>Income Vs Expense - {{ count($labels) }} days ago</b>
        </h3>

        <canvas id="farmerFinance" style="width: 100%;"></canvas>



        <script>
            $(function() {


                window.chartColors = {
                    red: 'rgb(255, 99, 132)',
                    orange: 'rgb(255, 159, 64)',
                    yellow: 'rgb(255, 205, 86)',
                    green: '#277C61',
                    blue: 'rgb(54, 162, 235)',
                    purple: 'rgb(153, 102, 255)',
                    grey: 'rgb(201, 203, 207)'
                };

                var chartData = {
                    labels: JSON.parse('<?php echo json_encode($labels); ?>'),
                    datasets: [{
                            type: 'bar',
                            label: 'Inome',
                            backgroundColor: window.chartColors.green,
                            data: {{ json_encode($income) }}
                        },
                        {
                            borderColor: window.chartColors.red,
                            backgroundColor: window.chartColors.red,
                            type: 'bar',
                            label: 'Expence',
                            data: {{ json_encode($expence) }}
                        },

                    ]

                };

                var ctx = document.getElementById('farmerFinance').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        title: {
                            display: true,
                            text: 'Chart.js Combo Bar Line Chart'
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: true
                        }
                    }
                });
            });
        </script>







        <script>
            $(function() {


                window.chartColors = {
                    red: 'rgb(255, 99, 132)',
                    orange: 'rgb(255, 159, 64)',
                    yellow: 'rgb(255, 205, 86)',
                    green: '#277C61',
                    blue: 'rgb(54, 162, 235)',
                    purple: 'rgb(153, 102, 255)',
                    grey: 'rgb(201, 203, 207)'
                };

                var chartData = {
                    labels: JSON.parse('<?php echo json_encode($labels); ?>'),
                    datasets: [{
                        type: 'bar',
                        label: 'Milk collection',
                        backgroundColor: window.chartColors.green,
                        data: {{ json_encode($data) }}
                    }]

                };

                var ctx = document.getElementById('farmerMilkCollection').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        title: {
                            display: true,
                            text: 'Chart.js Combo Bar Line Chart'
                        },
                        tooltips: {
                            mode: 'index',
                            intersect: true
                        }
                    }
                });
            });
        </script>






    </div>
</div>
