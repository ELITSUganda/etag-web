<style>
    .canvasjs-chart-credit {
        display: none !important;
    }
</style><?php

$title = isset($_title) ? $_title : 'Farm Count by Districts';
$subTitle = isset($_subTitle) ? $_subTitle : 'As of November, 2017';

?>
<div class="card mb-5"
    style="border-radius: 10px; border: 5px #6A3A00 solid; box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;">
    <div class="card-body p-0">
        <p class="text-bold mb-0 mb-md-0 pb-0 text-primary pl-4 pt-1 "
            style="font-weight: 700; font-size: 2.5rem; height: 3rem;">
            {{ $title }}</p>

        {{-- create a flex with space between --}}
        <div class="d-flex justify-content-between">
            <small class="px-4 py-0 m-0 "><b>{{ $subTitle }}.</b></small>
            <u><a href="{{ admin_url('farms') }}" class="px-4 py-0 m-0 text-primary"><b>View All</b></a></u>
        </div>
        <hr class="mt-0 mb-0 pb-0" style="height: 5px; background-color: #6A3A00;">
        <div id="animals-by-farms" class="" style="height: 470px; width: 100%;"></div>
    </div>
</div>
<script>
    $(document).on('pjax:complete', function() {

        my_function_2();
        // Your code to execute after PJAX content is loaded into the container
    });
    //document.addEventListener("DOMContentLoaded", my_function);
    document.addEventListener("DOMContentLoaded", function() {
        my_function_2();
    });

    var hasLoaded = false;

    function my_function_2() {
        var chart = new CanvasJS.Chart("animals-by-farms", {
            backgroundColor: "transparent",
            axisX: {
                lineThickness: 0,
                tickLength: 0,
                labelFormatter: function(e) {
                    return "";
                }
            },
            axisY: {
                lineThickness: 5,
                gridThickness: 0,
                tickLength: 1,
            },

            data: [{
                type: "bar",
                startAngle: 40,
                toolTipContent: "<b>{label}</b>: {y}",
                indexLabelFontSize: 12,
                indexLabel: "{label} ({y})",
                labelFontSize: 20,
                dataPoints: JSON.parse('<?= json_encode($data) ?>'),
            }]
        });

        chart.render();
        return;

        hasLoaded = true;
        var options = {
            /*  title: {
                 text: '<?= $title ?>',
             },
             subtitles: [{
                 text: "As of November, 2017",
             }], */
            theme: "light3",
            animationEnabled: true,
            axisY: {
                lineThickness: 0,
                gridThickness: 0,
                tickLength: 0,
                labelFormatter: function(e) {
                    return "";
                }
            },
            data: [{
                type: "bar",
                startAngle: 40,
                toolTipContent: "<b>{label}</b>: {y}",
                indexLabelFontSize: 12,
                indexLabel: "{label} ({y})",
                labelFontSize: 20,
                dataPoints: JSON.parse('<?= json_encode($data) ?>')
            }]
        };
        $("#animals-by-farms").CanvasJSChart(options);
    }
</script>
