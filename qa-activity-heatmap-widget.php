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
            // handling no exam case
            $data[] = [
                'date' => time() * 1000,
                'value' => 0
            ];
        }

        return $data;
    }

    function output_widget($region, $place, $themeobject, $template, $request, $qa_content) {
        $handle = qa_request_part(1); 
        $target_userid = qa_handle_to_userid($handle);
        
        if (!$target_userid) {
            $themeobject->output('<div class="qa-widget-content">No user found.</div>');
            return;
        }

        $exam_data = $this->get_exam_data($target_userid); 
        $json = json_encode($exam_data);

        $themeobject->output('<div style="margin: auto;">');
        $themeobject->output('<div id="activity-heatmap-tooltip" class="ch-plugin-tooltip"></div>');
        $themeobject->output('
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
        <script>
        // Store the data globally so the layer can access it
        window.examHeatmapData = $json;
        
        // Function to initialize heatmap (will be called by the layer)
        window.initActivityHeatmap = function() {
            if (typeof CalHeatmap === 'undefined') {
                console.error('CalHeatmap not loaded');
                return;
            }

            const data = window.examHeatmapData || [];
            // console.log('Initializing heatmap with data:', data);

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
                            // scheme: 'Reds',
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
            );
        };
        </script>
        ");
    }
}