<?php
class qa_activity_heatmap_widget {
    function allow_template($template) { return true; }
    function allow_region($region) { return true; }

    function get_exam_data($userid) {
        $result = qa_db_read_all_assoc(qa_db_query_sub(
            "SELECT DATE(datetime) AS date, COUNT(*) AS count
             FROM userexam
             WHERE userid = #
             GROUP BY DATE(datetime)",$userid
        ));

        $data = [];
        foreach ($result as $row) {
            $timestamp = strtotime($row['date']);
            $data[] = ['t' => $timestamp, 'v' => (int)$row['count']];
        }

        return json_encode($data);
    }
    

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content) {
        $userid = qa_get_logged_in_userid();
        if (!$userid) return;
        // Add real data from DB here as needed...
        //$json = get_exam_data(1); // replace with $userid()

        $themeobject->output('<link rel="stylesheet" href="https://unpkg.com/cal-heatmap/cal-heatmap.css" />');
        $themeobject->output('<script src="https://unpkg.com/d3@7/dist/d3.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/dayjs/dayjs.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/cal-heatmap/dist/plugins/tooltip.min.js"></script>');
    
        $themeobject->output('<div style="margin: auto;">');
        $themeobject->output('<div id="activity-heatmap" style=" padding: 10px; box-sizing: border-box; width=100%; background-color: #f9f9f9; min-height: 150px;"></div>');
        $themeobject->output('</div>');
        $themeobject->output("
        <style>
            #activity-heatmap {
                overflow-x: auto;
                white-space: nowrap;
                padding: 10px;
                // border: 1px solid #fcc;
                border: 1px solid transparent;
                margin: 1px 1px 1px 1px;
            }
            .highlight-today rect{
                stroke: #000;
                stroke-width: 2px;
            }    
        </style>
        <script>
        window.addEventListener('load', function() {

            // Sample data for testing
            const data = [];
            // Generate Sample data
            for (let month = 0; month < 12; month++) { 
                const daysInMonth = new Date(2025, month + 1, 0).getDate();
                for (let day = 1; day <= daysInMonth; day++) {
                    const ts = new Date(2025, month, day).getTime(); // ms
                    data.push({
                    date: Math.floor(ts),                 
                    value: Math.floor(Math.random() * 8)    // 0 to 7
                    });
                }
            }

            // var data = $json;
            // console.log('Heatmap data:', data);

            const cal = new CalHeatmap();
            const now = new Date();
            const sixMonthsAgo = new Date(now.getFullYear(), now.getMonth() - 6, 1);

            cal.paint({
                itemSelector: '#activity-heatmap',
                data: {
                    source: data,
                    type: 'json',
                    x: 'date',
                    y: 'value'
                },
                date: {
                    start: sixMonthsAgo,
                    step: 1,
                    unit: 'day',
                    highlight: [
                        new Date(),
                    ],    
                },
                domain: {
                    type: 'month',
                    gutter: 2
                },
                subDomain: {
                    type: 'day',
                    width: 15,
                    height: 15,
                    radius: 2
                },
                scale: {
                    color: {
                        type: 'linear',
                        domain: [0, 7],
                        scheme: 'Blues', //predifined RdBu color scheme
                        //range: ['rgba(186, 186, 186, 1)', '#c73a3aff'], // Light to deep red
                    }
                },
            },
            [[Tooltip, {
                    enabled: true,
                    text: function(timestamp, value, dayjsDate) {
                        const date = dayjs(timestamp);
                        const formattedDate = date.format('( ddd ) MMM D, YYYY');
                        // console.log('Tooltip - timestamp:', timestamp, 'formatted:', formattedDate, 'value:', value);
                        if (value === 1) {
                            return formattedDate + ' - ' + ' 1 exam taken';
                        }
                        if (value === null || value === 0) {
                            return formattedDate + ' - No exams taken';
                        }
                        return formattedDate + ' - ' + (value) + ' exams taken';
                    }
                }
            ]]
            )
        });
        </script>
    ");
    }
}