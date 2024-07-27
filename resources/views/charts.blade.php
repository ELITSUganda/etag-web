 <div id="chartContainer" style="height: 370px; width: 100%;"></div>

 <script>
     $(document).on('pjax:complete', function() {

         my_function();
         // Your code to execute after PJAX content is loaded into the container
     });
     //document.addEventListener("DOMContentLoaded", my_function);
     document.addEventListener("DOMContentLoaded", function() {
         my_function();
     });

     var hasLoaded = false;

     function my_function() {
         if (hasLoaded) {
            // return;
         }
         hasLoaded = true;
         //alert('PJAX content has been loaded into the container');
         var options = {
             title: {
                 text: "Desktop OS Market Share",
                 backgroundColor: "#f5f5f5",
             },
             subtitles: [{
                 text: "As of November, 2017"
             }],
             theme: "light2",
             animationEnabled: true,
             data: [{
                 type: "bar",
                 startAngle: 40,
                 toolTipContent: "<b>{label}</b>: {y}%",
                 showInLegend: "true",
                 legendText: "{label}",
                 indexLabelFontSize: 16,
                 indexLabel: "{label} - {y}%",
                 dataPoints: [{
                         y: 48.36,
                         label: "Windows 7"
                     },
                     {
                         y: 26.85,
                         label: "Windows 10"
                     },
                     {
                         y: 1.49,
                         label: "Windows 8"
                     },
                     {
                         y: 6.98,
                         label: "Windows XP"
                     },
                     {
                         y: 6.53,
                         label: "Windows 8.1"
                     },
                     {
                         y: 2.45,
                         label: "Linux"
                     },
                     {
                         y: 3.32,
                         label: "Mac OS X 10.12"
                     },
                     {
                         y: 4.03,
                         label: "Others"
                     }
                 ]
             }]
         };
         $("#chartContainer").CanvasJSChart(options);

     }
 </script>
