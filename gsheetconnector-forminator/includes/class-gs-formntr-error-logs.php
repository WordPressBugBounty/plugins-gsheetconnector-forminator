<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create error log table
 * NOTE: Call this from main plugin file on activation
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
// phpcs:disable WordPress.DB.DirectDatabaseQuery.NoCaching
function gsfrmnf_create_error_log_table()// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
{
    global $wpdb;

    $table = $wpdb->prefix . 'gsformntr_error_logs';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        error_id VARCHAR(191) NOT NULL,
        code INT NOT NULL,
        message TEXT NOT NULL,
        details LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY error_id (error_id),
        KEY code (code)
    ) {$charset};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

if (!class_exists('GSCFORMNTR_Free_Error_Logs')) {

    class GSCFORMNTR_Free_Error_Logs
    {

        public function __construct()
        {
            add_action('admin_post_gsfrmnf_clear_logs', [$this, 'clear_logs']);
            add_action('admin_post_gsfrmnf_download_logs', [$this, 'download_logs']);
        }

        /* =====================================================
        * STATIC ENTRY POINT
        * ===================================================== */
        public static function render_page()
        {
            (new self())->gsfrmnf_render_page_html();
        }

        /* =====================================================
        * MAIN DB LOGGER
        * ===================================================== */
        public static function log_to_db($error_id, $code, $message, $details = [])
        {
            global $wpdb;

            $table = $wpdb->prefix . 'gsformntr_error_logs';

           $result = $wpdb->get_var(// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                "SHOW TABLES LIKE '{$wpdb->esc_like( $table )}'"
            );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

            if ( empty( $result ) ) {
                return false;
            }

              if (is_string($details)) {
                $decoded = json_decode($details, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $details = $decoded; 
                } else {
                    $details = ['raw_error' => $details];
                }
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- $table is $wpdb->prefix + a static literal, never user input; table/column identifiers can't be parameterized via $wpdb->prepare() placeholders.
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                return false;
            }

            /* IMPORTANT FIX START */
            if (is_string($details)) {
                $decoded = json_decode($details, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $details = $decoded; /* already JSON → convert to array */
                } else {
                    $details = ['raw_error' => $details];
                }
            }

            // Prevent duplicate error log entries for identical errors within 30 minutes.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table is $wpdb->prefix + a static literal, never user input.
            $recent_log = $wpdb->get_var(
                $wpdb->prepare(
                    // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- $table is $wpdb->prefix + a static literal, never user input; actual dynamic values are passed via %s/%d placeholders.
                    "SELECT COUNT(*) FROM {$table} WHERE error_id = %s AND code = %d AND message = %s AND created_at >= %s",
                    $error_id,
                    $code,
                    $message,
                    wp_date(
                        'Y-m-d H:i:s',
                        time() - (30 * MINUTE_IN_SECONDS)
                    )
                )
            );


            if (!empty($recent_log)) {
                return false;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- $wpdb->insert() is WordPress's own safe API for writing to this plugin's custom error-log table; values are already escaped via the format array.
            return $wpdb->insert(
                $table,
                [
                    'error_id'   => (string) $error_id,
                    'code'       => (int) $code,
                    'message'    => (string) $message,
                    'details'    => wp_json_encode($details, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    'created_at' => current_time('mysql'),
                ],
                ['%s', '%d', '%s', '%s', '%s']
            );
        }


        /**
         * Capture request context for error logging
         */
        public static function get_request_context()
        {
           
            return [
                    'request_url'    => esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ),
                    'request_method' => sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ),
                    'status_code'    => http_response_code(),
                    'remote_ip'      => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
                    'user_agent'     => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
                    'referrer'       => esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ),
                    'timestamp'      => current_time('mysql'),
                ];
        }


        /* =====================================================
        * DEBUG → DB NORMALIZER
        * ===================================================== */
        public static function formntr_log_from_debug($error)
        {
            
            if (is_string($error)) {
                $decoded = json_decode($error, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $error = $decoded;
                }
            }

            if (is_array($error) || is_object($error)) {

                self::log_to_db(
                    'forminator_gsheet_error',
                    500,
                    'Forminator Google Sheets Error',
                    (array) $error
                );
            } else {

                self::log_to_db(
                    'forminator_gsheet_error',
                    500,
                    'Forminator Google Sheets Error',
                    [
                        'type'      => 'error',
                        'raw_error' => trim((string) $error),
                    ]
                );
            }
        }


        /* =====================================================
        * ADMIN PAGE
        * ===================================================== */
        public function gsfrmnf_render_page_html()
        {
           
            global $wpdb;
            $table = esc_sql( $wpdb->prefix . 'gsformntr_error_logs' );

            if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
                echo '<div class="notice notice-error"><p>Log table not found.</p></div>';
                return;
            }

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $logs = $wpdb->get_results(
                "SELECT * FROM `{$table}` ORDER BY created_at DESC",// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                ARRAY_A
            );

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $count_log = $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}`" );
            ?>
            <div class="error-log-main shadow-box mt-40 p-30">

                <div class="error-log-head flex-wrap gap-20">
                    <div>
                        <div class="heading mt-0"><?php echo esc_html__("Error Log", 'gsheetconnector-forminator'); ?> </div>
                        <p><?php echo esc_html__("Error logs are saved in the database. Please clear them regularly to avoid increasing the database size.", 'gsheetconnector-forminator'); ?></p>
                    </div>
                    <?php if ( $count_log > 0 ) { ?> 
                    <div class="errorlog-button-list">

                        <div class="gsfrmnf-copy-logs-loading"> </div>
                        <button type="button" id="gsfrmnf-clear-logs-btn" class="button btn-logs">
                        <?php echo esc_html__("Clear Logs", 'gsheetconnector-forminator'); ?>
                        </button>

                        <a href="<?php echo esc_url(
                                        wp_nonce_url(
                                            admin_url('admin-post.php?action=gsfrmnf_download_logs'),
                                            'gsc_download_logs_nonce'
                                        )
                                    ); ?>" class="button button-primary"><?php echo esc_html__("Download CSV", 'gsheetconnector-forminator'); ?></a>

                        <button type="button" id="gsfrmnf-copy-logs" class="button btn-logs"><?php echo esc_html__("Copy Logs", 'gsheetconnector-forminator'); ?></button>
                        <div class="gsc-copy-msg d-none"></div>
                    </div>
                    <?php } ?>
                </div> 
                <!-- error head #end -->
                <div class="debug-log-div">
                    <table class="widefat striped gsfrmnf-error-log-table mt-30">
                        <thead>
                            <tr>
                                <th><?php echo esc_html__("Date", 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__("Error ID", 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__("Code", 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__("Message", 'gsheetconnector-forminator'); ?></th>
                                <th><?php echo esc_html__("Details", 'gsheetconnector-forminator'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($logs): foreach ($logs as $log): ?>
                                    <tr>
                                        <td><?php
                                            $format = get_option('date_format') . ' ' . get_option('time_format');

                                            /* mysql2date() returns formatted date using WP timezone settings */
                                            echo esc_html(mysql2date($format, $log['created_at'], false));
                                            ?></td>
                                        <td><?php echo esc_html($log['error_id']); ?></td>
                                        <td>
                                            <span class="sb-error-code" data-code="<?php echo esc_attr($log['code']); ?>">
                                                <?php echo esc_html($log['code']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo esc_html($log['message']); ?></td>
                                        <td>
                                            <?php
                                            $details = json_decode($log['details'], true);

                                            if (json_last_error() === JSON_ERROR_NONE && is_array($details)) :
                                            ?>
                                                <div class="gsc-error-details">
                                                    <div class="more-error-display">

                                                        <?php
                                                        $decoded = json_decode($log['details'], true);
                                                        $display = '';

                                                        if (is_array($decoded) && !empty($decoded['raw_error'])) {

                                                            $raw = $decoded['raw_error'];

                                                            /* Extract text after "message:" */
                                                            if (strpos($raw, 'message:') !== false) {
                                                                $parts = explode('message:', $raw);
                                                                $display = trim(end($parts));
                                                            } else {
                                                                $display = $raw;
                                                            }
                                                        } else {
                                                            $display = $log['details'];
                                                        }

                                                        echo esc_html($display);
                                                        ?>
                                                    </div>
                                                </div>

                                            <?php else: ?>
                                                <?php echo esc_html($log['details']); ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center"><?php echo esc_html__("No logs found", 'gsheetconnector-forminator'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div> <!-- deubg logo div show / hide --->
                <script>
                    jQuery(document).ready(function($) {

                        var debugStateKey = 'debug_log_state';

                        /* ---------- More info toggle (UNCHANGED) ---------- */
                        $('.more-error-display').each(function() {

                            var box = $(this);
                            var maxHeight = 75;

                            box.css({
                                'max-height': maxHeight + 'px',
                                'overflow': 'hidden'
                            });

                            var clone = box.clone();
                            clone.css({
                                'max-height': 'none',
                                'height': 'auto',
                                'position': 'absolute',
                                'visibility': 'hidden',
                                'overflow': 'visible'
                            });

                            $('body').append(clone);

                            if (clone.outerHeight() > maxHeight) {
                                if (box.next('.more-error-toggle').length === 0) {
                                    var link = $('<a href="#" class="more-error-toggle">More info</a>');
                                    box.after(link);
                                }
                            }

                            clone.remove();
                        });

                        $(document).on('click', '.more-error-toggle', function(e) {
                            e.preventDefault();

                            var link = $(this);
                            var box = link.prev('.more-error-display');

                            if (box.hasClass('expanded')) {
                                box.removeClass('expanded').css('max-height', '75px');
                                link.text('More info');
                            } else {
                                box.addClass('expanded').css('max-height', 'none');
                                link.text('Less info');
                            }
                        });


                    });
                </script>



            </div>
<?php
        }

        /* =====================================================
        * ACTIONS
        * ===================================================== */
        public function clear_logs()
        {
            if (!current_user_can('manage_options')) {
                wp_die('Permission denied.');
            }

            check_admin_referer('gsc_clear_logs_nonce');
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}gsformntr_error_logs");

            wp_safe_redirect(wp_get_referer());
            exit;
        }


        public static function log_js_error()
        {
            if (!current_user_can('manage_options')) {
                wp_send_json_error();
            }

            $log = $_POST['log'] ?? [];// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

            if (is_string($log)) {
                $decoded = json_decode($log, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $log = $decoded;
                }
            }

            self::log_to_db(
                'js_error',
                intval($log['status'] ?? 400),
                sanitize_text_field($log['message'] ?? 'JavaScript Error'),
                [
                    'type'    => $log['type'] ?? 'js',
                    'request' => self::get_request_context(),
                    'payload' => $log,
                ]
            );

            wp_send_json_success();
        }


        public function download_logs()
        {

            if (! current_user_can('manage_options')) {
                wp_die(esc_html__('Permission denied.', 'gsheetconnector-forminator'));
            }

            check_admin_referer('gsc_download_logs_nonce');

            global $wpdb;
            $table = esc_sql( $wpdb->prefix . 'gsformntr_error_logs' );

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $logs = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `{$table}`" ), ARRAY_A );

            if (empty($logs)) {
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            nocache_headers();

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=gsc-error-logs.csv');

            $output = fopen('php://output', 'w');

            if (false === $output) {
                exit;
            }

            /*  CSV Header Row */
            fputcsv($output, array('Date', 'Error ID', 'Code', 'Message', 'Details'));

            foreach ($logs as $log) {
                fputcsv($output, array(
                    $log['created_at'],
                    $log['error_id'],
                    $log['code'],
                    $log['message'],
                    $log['details'],
                ));
            }

            /* fclose optional here (php://output auto closes) */
            exit;
        }
    }

    new GSCFORMNTR_Free_Error_Logs();
}
add_action('wp_ajax_gsc_log_js_error', ['GSCFORMNTR_Free_Error_Logs', 'log_js_error']);
