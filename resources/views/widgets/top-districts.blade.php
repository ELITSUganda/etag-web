<style>
    .canvasjs-chart-credit {
        display: none !important;
    }
</style><?php

$title = isset($_title) ? $_title : 'Farm Count by Districts';

?>
<div class="card mb-5"
    style="border-radius: 10px; border: 5px #6A3A00 solid; box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;">
    <div class="card-body p-0">
        <p class="text-bold mb-0 mb-md-0 pb-0 text-primary pl-4 pt-1 "
            style="font-weight: 700; font-size: 2.5rem; height: 3rem;">
            {{ $title }}</p>

        {{-- create a flex with space between --}}
        <div class="d-flex justify-content-between">
            <small class="px-4 py-0 m-0 "><b>Top 25 districts ranked by number of farms.</b></small>
            <u><a href="{{ admin_url('locations') }}" class="px-4 py-0 m-0 text-primary"><b>View All</b></a></u>
        </div>
        <hr class="mt-0  mb-0 pb-0" style="height: 5px; background-color: #6A3A00;">
        <div id="topDistricts" style="height: 470px; width: 100%;"></div>
    </div>
</div>
<script>
    $(document).on('pjax:complete', function() {



        // get last par of the url
        var url = window.location.href;
        var parts = url.split('/');
        var last_part = parts[parts.length - 1];
        if (last_part == '') {
            my_function();
        }
        // Your code to execute after PJAX content is loaded into the container
    });
    //document.addEventListener("DOMContentLoaded", my_function);
    document.addEventListener("DOMContentLoaded", function() {

        // get last par of the url
        var url = window.location.href;
        var parts = url.split('/');
        var last_part = parts[parts.length - 1];
        if (last_part == '') {
            my_function();
        }
    });

    var hasLoaded = false;

    function my_function() {
        if (hasLoaded) {
            // return;
        }
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
            data: [{
                type: "pie",
                startAngle: 40,
                toolTipContent: "<b>{label}</b>: {y}",
                legendText: "{label}",
                indexLabelFontSize: 12,
                indexLabel: "{label} {y}",
                labelFontSize: 20,
                dataPoints: JSON.parse('<?= json_encode($data) ?>')
            }]
        };
        $("#topDistricts").CanvasJSChart(options);
    }
</script>
