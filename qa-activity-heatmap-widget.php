<?php
class qa_activity_heatmap_widget {
    function allow_template($template) { return true; }
    function allow_region($region) { return true; }

    function get_exam_data($userid) {
        $result = qa_db_read_all_assoc(qa_db_query_sub(
            "SELECT DATE(datetime) AS exam_date, COUNT(*) AS exams_given
            FROM qa_exam_results
            WHERE userid = #
            GROUP BY DATE(datetime)
            ORDER BY DATE(datetime) DESC",
            $userid
        ));

        $data = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $timestamp = strtotime($row['exam_date']);
                $exams = (int)$row['exams_given'];
                if ($timestamp !== false) {
                    $data[] = [
                        'date' => $timestamp*1000,
                        'value' => $exams
                    ];
                }
            }
        } else {
            // no exam data → safe default
            $data[] = [
                'date' => time(),
                'value' => 0
            ];
        }

        // echo the JSON once for JS usage
        $json = json_encode($data);
        echo "
        <script>
            var examData = $json;
            console.log('Exam Data:', examData);
        </script>";

        return $data;
    }
    

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content) {
        $handle = qa_request_part(1); 
        $target_userid = qa_handle_to_userid($handle);
        echo "<script>console.log('User ID: " . $target_userid . "');</script>";
        if (!$target_userid) {
            $themeobject->output('<div class="qa-widget-content">No user found.</div>');
            return;
        }
        // $json = get_exam_data($target_userid); 
        $exam_data = $this->get_exam_data($target_userid); 
        $json = json_encode($exam_data);
        echo "<script>console.log('JSON Data: " . $json . "');</script>";

        $themeobject->output('<link rel="stylesheet" href="https://unpkg.com/cal-heatmap/cal-heatmap.css" />');
        $themeobject->output('<script src="https://unpkg.com/d3@7/dist/d3.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/dayjs/dayjs.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/cal-heatmap/dist/plugins/tooltip.min.js"></script>');
        $themeobject->output('<script src="https://unpkg.com/cal-heatmap/dist/plugins/CalendarLabel.min.js"></script>');
    
        $themeobject->output('<div style="margin: auto;">');
        $themeobject->output('<div id="activity-heatmap-tooltip" class="ch-plugin-tooltip"></div>');
        $themeobject->output('
            <div id="activity-heatmap-tooltip" class="ch-plugin-tooltip"></div>
                <div id="activity-heatmap" 
                    style="
                        padding: 10px; 
                        box-sizing: border-box; 
                        background-color: #f9f9f9; 
                        min-height: 150px; 
                        max-width: 1400px; 
                        margin: 5px auto; 
                        width: 100%;
                    ">
                </div>
            </div>
        ');

        $themeobject->output("
        <style>
            #activity-heatmap-tooltip {
                position: fixed !important;
                z-index: 999999 !important;
                background: black !important;
                border: 1px solid #ccc;
                padding: 6px 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.15);
                pointer-events: none;
                font-size: 13px;
                display: none;
            }

            #activity-heatmap {
                overflow-x: auto;
                white-space: nowrap;
                padding: 10px;
                // border: 1px solid #fcc;
                border: 2px solid transparent;
                margin: 1px 1px 1px 1px;
            }
            .highlight rect{
                stroke: #000 !important;
                stroke-width: 3px !important;
            }
            /* Override any conflicting styles from cal-heatmap */
            .ch-plugin-tooltip {
                position: fixed !important;
                z-index: 999999 !important;
            }
            /* Ensure the heatmap container is positioned correctly */
            div:has(#activity-heatmap) {
                position: relative;
                z-index: auto;
            }    
        </style>
        <script>
        window.addEventListener('load', function() {

            // // Sample data for testing
            // const data = [];
            // // Generate Sample data
            // for (let month = 0; month < 1; month++) { 
            //     const daysInMonth = new Date(2025, month + 1, 0).getDate();
            //     for (let day = 1; day <= 3; day++) {
            //         const ts = new Date(2025, month, day).getTime(); // ms
            //         data.push({
            //             date: Math.floor(ts),                 
            //             value: Math.floor(Math.random() * 4)    // 0 to 3
            //         });
            //     }
            // }

            const data = $json;
            console.log('Heatmap data:', data);

            const cal = new CalHeatmap();
            const now = new Date();
            const xMonthsAgo = new Date(now.getFullYear(), now.getMonth() - 12, 1);

            cal.paint(
                {
                    itemSelector: '#activity-heatmap',
                    data: {
                        source: data,
                        type: 'json',
                        x: 'date',
                        y: 'value'
                    },
                    range: 15,
                    date: {
                        start: xMonthsAgo,
                        end: now,
                        step: 1,
                        unit: 'day',
                        highlight: [
                            new Date(),
                        ],
                        locale: {
                            weekStart: 1,
                        }    
                    },
                    domain: {
                        type: 'month',
                        gutter: 2
                    },
                    subDomain: {
                        type: 'day',
                        width: 16,
                        height: 16,
                        radius: 2
                    },
                    scale: {
                        color: {
                            type: 'linear',
                            domain: [0,2],
                            //scheme: 'Reds', //predifined RdBu color scheme
                            // range: ['rgba(186, 186, 186, 1)', '#c73a3aff'], // Light to deep red
                            range: ['#d6d6d6', '#5e2828ff'],
                        }
                    },
                },
                [
                    [Tooltip, 
                        {
                            enabled: true,
                            container: '#activity-heatmap-tooltip',
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
                    ],
                    [CalendarLabel,
                        {
                            position: 'left',
                            key: 'left',
                            text: () => ['Mon', '', '', 'Thu', '', '', 'Sun'],
                            textAlign: 'end',
                            width: 30,
                            padding: [0, 10, 0, 0],
                        },
                    ],
                ]
            )
        });
        </script>
    ");
    }
}