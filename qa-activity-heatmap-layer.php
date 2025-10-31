<?php
class qa_html_theme_layer extends qa_html_theme_base
{
    function head_css()
    {
        qa_html_theme_base::head_css();
        
        // Add cal-heatmap CSS to head
        $this->output('<link rel="stylesheet" href="https://unpkg.com/cal-heatmap/cal-heatmap.css" />');
        
        // Add custom CSS
        $this->output('
        <style>
            #activity-heatmap-tooltip {
                position: fixed !important;
                z-index: 999999 !important;
                background: black !important;
                color: white !important;
                border: 1px solid #ccc;
                padding: 6px 10px 0px 10px;
                box-shadow: 0 2px 6px rgba(0,0,0,0.15);
                pointer-events: none;
                font-size: 13px;
                display: none;
                border-radius: 4px;
            }

            #activity-heatmap {
                overflow-x: auto;
                white-space: nowrap;
                padding: 10px 5px 10px 10px;
                border: 2px solid transparent;
                box-sizing: border-box; 
                background-color: #fff !important;
                min-height: 150px; 
                max-width: 1550px; 
                margin: 5px auto; 
                width: 100%;
            }
            .ch-container {
                padding: 5px 15px 5px 10px; // top right bottom left
            }
            
            .highlight rect {
                stroke: #000 !important;
                stroke-width: 3px !important;
            }
            
            /* Override any conflicting styles from cal-heatmap */
            .ch-plugin-tooltip {
                position: fixed !important;
                z-index: 999999 !important;
            }
            .year-filter {
                padding: 5px 15px 5px 10px;
                border: 1px solid #ccc;
                border-radius: 4px;
                background: white;
                cursor: pointer;
                font-size: 14px;
            }

            
            /* Ensure the heatmap container is positioned correctly */
            div:has(#activity-heatmap) {
                position: relative;
                z-index: auto;
            }
            [data-theme="dark"] .ch-container,
            .dark .ch-container .year-filter{
                background-color: #36393f;
                color: #f9f9f9;
            }
            [data-theme="dark"] #activity-heatmap,
            .dark #activity-heatmap {
                background-color: #36393f !important;
            }       
        </style>
        ');
    }

    function head_script()
    {
        qa_html_theme_base::head_script();
        
        // Add external JavaScript libraries to head
        $this->output('<script src="https://unpkg.com/d3@7/dist/d3.min.js"></script>');
        $this->output('<script src="https://unpkg.com/dayjs/dayjs.min.js"></script>');
        $this->output('<script src="https://unpkg.com/@popperjs/core@2/dist/umd/popper.min.js"></script>');
        $this->output('<script src="https://unpkg.com/cal-heatmap/dist/cal-heatmap.min.js"></script>');
        $this->output('<script src="https://unpkg.com/cal-heatmap/dist/plugins/tooltip.min.js"></script>');
        $this->output('<script src="https://unpkg.com/cal-heatmap/dist/plugins/CalendarLabel.min.js"></script>');
    }

    function body_suffix()
    {
        qa_html_theme_base::body_suffix();
        
        // Initialize heatmap after all libraries are loaded
        $this->output('
        <script>
        // Initialize heatmap when DOM is ready and libraries are loaded
        document.addEventListener("DOMContentLoaded", function() {
            // Small delay to ensure all libraries are fully loaded
            setTimeout(function() {
                if (typeof window.initActivityHeatmap === "function") {
                    window.initActivityHeatmap();
                }
            }, 100);
        });
        
        // Fallback for window load event
        window.addEventListener("load", function() {
            setTimeout(function() {
                if (typeof window.initActivityHeatmap === "function" && 
                    document.getElementById("activity-heatmap") && 
                    !document.querySelector("#activity-heatmap svg")) {
                    window.initActivityHeatmap();
                }
            }, 200);
        });
        </script>
        ');
    }
}