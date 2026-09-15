<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Aystat_Honeypot_Admin_List_Table extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
                'singular' => 'log',
                'plural'   => 'logs',
                'ajax'     => false
        ] );
    }

    public static function get_date_bounds( $timeframe, $req_start = '', $req_end = '' ) {
        $now_ts     = current_time( 'timestamp' );
        $start_date = '';
        $end_date   = '';

        switch ( $timeframe ) {
            case 'yesterday':
                $start_date = gmdate( 'Y-m-d', strtotime( '-1 day', $now_ts ) );
                $end_date   = gmdate( 'Y-m-d', strtotime( '-1 day', $now_ts ) );
                break;
            case 'this_week':
                $start_date = gmdate( 'Y-m-d', strtotime( 'monday this week', $now_ts ) );
                $end_date   = gmdate( 'Y-m-d', strtotime( 'sunday this week', $now_ts ) );
                break;
            case 'last_week':
                $start_date = gmdate( 'Y-m-d', strtotime( 'monday last week', $now_ts ) );
                $end_date   = gmdate( 'Y-m-d', strtotime( 'sunday last week', $now_ts ) );
                break;
            case 'this_month':
                $start_date = gmdate( 'Y-m-01', $now_ts );
                $end_date   = gmdate( 'Y-m-t', $now_ts );
                break;
            case 'last_month':
                $start_date = gmdate( 'Y-m-01', strtotime( 'first day of last month', $now_ts ) );
                $end_date   = gmdate( 'Y-m-t', strtotime( 'last day of last month', $now_ts ) );
                break;
            case 'custom':
                if ( ! empty( $req_start ) && ! empty( $req_end ) ) {
                    $start_date = sanitize_text_field( $req_start );
                    $end_date   = sanitize_text_field( $req_end );
                }
                break;
            case 'today':
            default:
                $start_date = gmdate( 'Y-m-d', $now_ts );
                $end_date   = gmdate( 'Y-m-d', $now_ts );
                break;
        }

        return [ $start_date, $end_date ];
    }

    public function get_columns() {
        return [
                'time'       => esc_html__( 'Time', 'aystat-honeypot-admin' ),
                'ip_address' => esc_html__( 'IP Address', 'aystat-honeypot-admin' ),
                'user_agent' => esc_html__( 'User Agent', 'aystat-honeypot-admin' ),
                'username'   => esc_html__( 'Attempted Username', 'aystat-honeypot-admin' ),
                'password'   => esc_html__( 'Attempted Password', 'aystat-honeypot-admin' )
        ];
    }

    protected function column_default( $item, $column_name ) {
        return esc_html( $item[ $column_name ] );
    }

    public static function get_chart_data( $timeframe, $req_start, $req_end, $search_term, $group_by = 'day' ) {
        global $wpdb;
        $table_name = Aystat_Honeypot_Admin_DB::get_table_name();
        $where = "1=1";
        $args = [];

        if ( ! empty( $search_term ) ) {
            $search = '%' . $wpdb->esc_like( $search_term ) . '%';
            $where .= " AND (ip_address LIKE %s OR user_agent LIKE %s OR username LIKE %s OR password LIKE %s)";
            array_push( $args, $search, $search, $search, $search );
        }

        list( $start_date, $end_date ) = self::get_date_bounds( $timeframe, $req_start, $req_end );

        if ( $start_date && $end_date ) {
            $where .= " AND time >= %s AND time <= %s";
            array_push( $args, $start_date . ' 00:00:00', $end_date . ' 23:59:59' );
        }

        if ( $group_by === 'year' ) {
            $sql = "SELECT DATE_FORMAT(time, '%%Y') as log_date, COUNT(id) as total FROM {$table_name} WHERE {$where} GROUP BY DATE_FORMAT(time, '%%Y') ORDER BY log_date ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        } elseif ( $group_by === 'month' ) {
            $sql = "SELECT DATE_FORMAT(time, '%%Y-%%m') as log_date, COUNT(id) as total FROM {$table_name} WHERE {$where} GROUP BY DATE_FORMAT(time, '%%Y-%%m') ORDER BY log_date ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        } else {
            $sql = "SELECT DATE(time) as log_date, COUNT(id) as total FROM {$table_name} WHERE {$where} GROUP BY DATE(time) ORDER BY log_date ASC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        }

        if ( ! empty( $args ) ) {
            $sql = $wpdb->prepare( $sql, ...$args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        return $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
    }

    public function extra_tablenav( $which ) {
        if ( $which === 'top' ) {

            // phpcs:disable WordPress.Security.NonceVerification.Recommended
            $timeframe  = isset( $_REQUEST['timeframe'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['timeframe'] ) ) : 'today';
            $req_start  = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : '';
            $req_end    = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : '';
            $search     = isset( $_REQUEST['s'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) : '';
            // phpcs:enable WordPress.Security.NonceVerification.Recommended

            list( $start_val, $end_val ) = self::get_date_bounds( $timeframe, $req_start, $req_end );
            $is_readonly = ( $timeframe !== 'custom' ) ? 'readonly' : '';

            $start_ts  = strtotime( $start_val );
            $end_ts    = strtotime( $end_val );
            $diff_days = max( 1, round( ( $end_ts - $start_ts ) / 86400 ) );

            $group_by = 'day';
            $step     = '+1 day';
            $format   = 'Y-m-d';

            if ( $diff_days > 730 ) {
                $group_by = 'year';
                $step     = '+1 year';
                $format   = 'Y';
                $start_ts = strtotime( gmdate( 'Y-01-01', $start_ts ) );
            } elseif ( $diff_days > 90 ) {
                $group_by = 'month';
                $step     = '+1 month';
                $format   = 'Y-m';
                $start_ts = strtotime( gmdate( 'Y-m-01', $start_ts ) );
            }

            $chart_raw = self::get_chart_data( $timeframe, $req_start, $req_end, $search, $group_by );

            $chart_labels = [];
            $chart_counts = [];

            if ( $start_val && $end_val ) {
                $current_date = $start_ts;
                $date_map     = [];
                $max_points   = 100;
                $point_count  = 0;

                while ( $current_date <= $end_ts && $point_count < $max_points ) {
                    $key = gmdate( $format, $current_date );
                    $date_map[ $key ] = 0;
                    $current_date = strtotime( $step, $current_date );
                    $point_count++;
                }

                $end_key = gmdate( $format, $end_ts );
                if ( ! isset( $date_map[ $end_key ] ) ) {
                    $date_map[ $end_key ] = 0;
                }

                foreach ( $chart_raw as $row ) {
                    $db_date = $row['log_date'];
                    if ( isset( $date_map[ $db_date ] ) ) {
                        $date_map[ $db_date ] = (int) $row['total'];
                    }
                }

                $chart_labels = array_keys( $date_map );
                $chart_counts = array_values( $date_map );
            } else {
                foreach ( $chart_raw as $row ) {
                    $chart_labels[] = $row['log_date'];
                    $chart_counts[] = (int) $row['total'];
                }
            }
            ?>

            <div style="position: relative; margin-top: 15px; margin-bottom: 15px;">
                <div>
                    <div class="alignleft actions" style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center; width: 100%;">
                        <select name="timeframe" id="hp-timeframe">
                            <option value="today" <?php selected( $timeframe, 'today' ); ?>><?php esc_html_e( 'Today', 'aystat-honeypot-admin' ); ?></option>
                            <option value="yesterday" <?php selected( $timeframe, 'yesterday' ); ?>><?php esc_html_e( 'Yesterday', 'aystat-honeypot-admin' ); ?></option>
                            <option value="this_week" <?php selected( $timeframe, 'this_week' ); ?>><?php esc_html_e( 'This Week', 'aystat-honeypot-admin' ); ?></option>
                            <option value="last_week" <?php selected( $timeframe, 'last_week' ); ?>><?php esc_html_e( 'Last Week', 'aystat-honeypot-admin' ); ?></option>
                            <option value="this_month" <?php selected( $timeframe, 'this_month' ); ?>><?php esc_html_e( 'This Month', 'aystat-honeypot-admin' ); ?></option>
                            <option value="last_month" <?php selected( $timeframe, 'last_month' ); ?>><?php esc_html_e( 'Last Month', 'aystat-honeypot-admin' ); ?></option>
                            <option value="custom" <?php selected( $timeframe, 'custom' ); ?>><?php esc_html_e( 'Custom', 'aystat-honeypot-admin' ); ?></option>
                        </select>

                        <input type="date" name="start_date" id="hp-start-date" value="<?php echo esc_attr( $start_val ); ?>" <?php echo esc_attr( $is_readonly ); ?>>
                        <input type="date" name="end_date" id="hp-end-date" value="<?php echo esc_attr( $end_val ); ?>" <?php echo esc_attr( $is_readonly ); ?>>

                        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search...', 'aystat-honeypot-admin' ); ?>" style="min-width: 200px;">

                        <?php submit_button( esc_html__( 'Search', 'aystat-honeypot-admin' ), 'button', 'filter_action', false ); ?>
                        <a href="?page=aystat-honeypot-admin&tab=logs&timeframe=today" class="button"><?php esc_html_e( 'Reset', 'aystat-honeypot-admin' ); ?></a>
                    </div>

                    <div class="alignleft actions" style="width: 100%; background: #fff; padding: 15px; border: 1px solid #ccd0d4; box-sizing: border-box;">
                        <h3 style="margin: 0 0 10px 0; font-size: 14px; color: #3c434a;"><?php esc_html_e( 'Activity Overview', 'aystat-honeypot-admin' ); ?></h3>
                        <div style="position: relative; height: 160px; width: 100%;">
                            <canvas id="hpLogsChart"></canvas>
                        </div>
                    </div>

                    <div style="clear: both;"></div>
                </div>
            </div>

            <div class="alignleft actions" style="margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; width: 100%; background: #fff; padding: 10px; border: 1px solid #ccd0d4; box-sizing: border-box;">
                <strong><?php esc_html_e( 'Export CSV:', 'aystat-honeypot-admin' ); ?></strong>

                <label><input type="checkbox" name="export_cols[]" value="time" checked> <?php esc_html_e( 'Time', 'aystat-honeypot-admin' ); ?></label>
                <label><input type="checkbox" name="export_cols[]" value="ip_address" checked> <?php esc_html_e( 'IP', 'aystat-honeypot-admin' ); ?></label>
                <label><input type="checkbox" name="export_cols[]" value="user_agent" checked> <?php esc_html_e( 'User Agent', 'aystat-honeypot-admin' ); ?></label>
                <label><input type="checkbox" name="export_cols[]" value="username" checked> <?php esc_html_e( 'Username', 'aystat-honeypot-admin' ); ?></label>
                <label><input type="checkbox" name="export_cols[]" value="password" checked> <?php esc_html_e( 'Password', 'aystat-honeypot-admin' ); ?></label>

                <?php wp_nonce_field( 'honeypot_export_csv', 'export_csv_nonce' ); ?>
                <?php submit_button( esc_html__( 'Export CSV', 'aystat-honeypot-admin' ), 'primary', 'export_csv', false ); ?>
            </div>

            <?php
            $labels_json = wp_json_encode( $chart_labels );
            $counts_json = wp_json_encode( $chart_counts );
            $label_text  = esc_js( esc_html__( 'Failed Attempts', 'aystat-honeypot-admin' ) );

            $dynamic_script = "var hpChartVars = { labels: {$labels_json}, data: {$counts_json}, title: '{$label_text}' };";

            $logs_script = <<<'JS'
            (function($) {
                $(document).ready(function() {
                    function hpUpdateDates() {
                        var timeframe = $('#hp-timeframe').val();
                        var $start = $('#hp-start-date');
                        var $end = $('#hp-end-date');

                        if (timeframe === 'custom') {
                            $start.prop('readonly', false);
                            $end.prop('readonly', false);
                            return;
                        }

                        $start.prop('readonly', true);
                        $end.prop('readonly', true);

                        var d = new Date();
                        var startDate = new Date();
                        var endDate = new Date();

                        function formatDate(date) {
                            var d = new Date(date),
                                month = '' + (d.getMonth() + 1),
                                day = '' + d.getDate(),
                                year = d.getFullYear();

                            if (month.length < 2) month = '0' + month;
                            if (day.length < 2) day = '0' + day;

                            return [year, month, day].join('-');
                        }

                        switch (timeframe) {
                            case 'today': break;
                            case 'yesterday':
                                startDate.setDate(d.getDate() - 1);
                                endDate.setDate(d.getDate() - 1);
                                break;
                            case 'this_week':
                                var day = d.getDay() || 7;
                                startDate.setDate(d.getDate() - (day - 1));
                                endDate = new Date(startDate);
                                endDate.setDate(startDate.getDate() + 6);
                                break;
                            case 'last_week':
                                var day = d.getDay() || 7;
                                startDate.setDate(d.getDate() - day - 6);
                                endDate = new Date(startDate);
                                endDate.setDate(startDate.getDate() + 6);
                                break;
                            case 'this_month':
                                startDate = new Date(d.getFullYear(), d.getMonth(), 1);
                                endDate = new Date(d.getFullYear(), d.getMonth() + 1, 0);
                                break;
                            case 'last_month':
                                startDate = new Date(d.getFullYear(), d.getMonth() - 1, 1);
                                endDate = new Date(d.getFullYear(), d.getMonth(), 0);
                                break;
                        }

                        $start.val(formatDate(startDate));
                        $end.val(formatDate(endDate));
                    }

                    $('#hp-timeframe').on('change', hpUpdateDates);

                    var canvas = document.getElementById('hpLogsChart');
                    if (canvas && typeof hpChartVars !== 'undefined') {
                        var ctx = canvas.getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: hpChartVars.labels,
                                datasets: [{
                                    label: hpChartVars.title,
                                    data: hpChartVars.data,
                                    backgroundColor: 'rgba(34, 113, 177, 0.6)',
                                    borderColor: 'rgba(34, 113, 177, 1)',
                                    borderWidth: 1,
                                    borderRadius: 3
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: { precision: 0 }
                                    }
                                },
                                plugins: { legend: { display: false } }
                            }
                        });
                    }
                });
            })(jQuery);
JS;

            // FIXED: Added array() for dependencies, '1.0.0' for version, and true for footer to satisfy WP Scanner
            wp_register_script( 'aystat-hp-logs', false, array(), '1.0.0', true );
            wp_enqueue_script( 'aystat-hp-logs' );
            wp_add_inline_script( 'aystat-hp-logs', $dynamic_script . "\n" . $logs_script );
        }
    }

    public function prepare_items() {
        global $wpdb;
        $table_name   = Aystat_Honeypot_Admin_DB::get_table_name();
        $per_page     = 20;
        $current_page = $this->get_pagenum();

        $where = "1=1";
        $args  = [];

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        if ( ! empty( $_REQUEST['s'] ) ) {
            $search = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%';
            $where .= " AND (ip_address LIKE %s OR user_agent LIKE %s OR username LIKE %s OR password LIKE %s)";
            array_push( $args, $search, $search, $search, $search );
        }

        $timeframe = isset( $_REQUEST['timeframe'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['timeframe'] ) ) : 'today';
        $req_start = isset( $_REQUEST['start_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['start_date'] ) ) : '';
        $req_end   = isset( $_REQUEST['end_date'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['end_date'] ) ) : '';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        list( $start_date, $end_date ) = self::get_date_bounds( $timeframe, $req_start, $req_end );

        if ( $start_date && $end_date ) {
            $where .= " AND time >= %s AND time <= %s";
            array_push( $args, $start_date . ' 00:00:00', $end_date . ' 23:59:59' );
        }

        $sql       = "SELECT * FROM {$table_name} WHERE {$where} ORDER BY time DESC"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $count_sql = "SELECT COUNT(id) FROM {$table_name} WHERE {$where}"; // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        if ( ! empty( $args ) ) {
            $sql       = $wpdb->prepare( $sql, ...$args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $count_sql = $wpdb->prepare( $count_sql, ...$args ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        }

        $total_items = $wpdb->get_var( $count_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $offset      = ( $current_page - 1 ) * $per_page;
        $sql         .= $wpdb->prepare( " LIMIT %d OFFSET %d", $per_page, $offset ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        $this->items           = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter
        $this->_column_headers = array( $this->get_columns(), array(), array() );

        $this->set_pagination_args( array(
                'total_items' => $total_items,
                'per_page'    => $per_page,
                'total_pages' => ceil( $total_items / $per_page )
        ) );
    }
}