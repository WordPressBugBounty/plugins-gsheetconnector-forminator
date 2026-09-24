<?php

/**
 * Feed storage for GSheetConnector for Forminator Forms.
 *
 * Feeds are stored ONLY in the custom tables:
 *   {prefix}forminatorgs_feeds     - one row per feed (name / status / form)
 *   {prefix}forminatorgs_feedmeta  - one row per feed, feed_data = JSON blob
 *                                    (sheet_id, tab_id, sheet_name, tab_name, ...)
 *
 * The plugin never reads or writes the old post / postmeta rows at runtime.
 * `migrate()` performs a ONE-TIME copy of any pre-existing postmeta feeds
 * ('forminator_forms_feed' + 'forminator_forms_feed_details' + 'frmntr_feed_status')
 * into these tables. The `legacy_meta_id` column records where a row came from so
 * that old bookmarked URLs (?feed_id=<postmeta meta_id>) still resolve.
 *
 * A normalised feed row returned by this class:
 *   [
 *     'id'             => int
 *     'form_id'        => int
 *     'feed_name'      => string
 *     'status'         => int    // 1 = active, 0 = disabled
 *     'is_default'     => int
 *     'legacy_meta_id' => int|null
 *     'created_at'     => string  // MySQL datetime, set once by DEFAULT CURRENT_TIMESTAMP, '' if unknown
 *     'updated_at'     => string  // MySQL datetime, set once by DEFAULT CURRENT_TIMESTAMP, '' if unknown
 *     'data'           => array  // decoded feed_data
 *   ]
 *
 * @since 2.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class GS_FORMNTR_Feed_Store
{
    /* Legacy postmeta keys - read once by migrate(), never at runtime. */
    const LEGACY_FEED_KEY    = 'forminator_forms_feed';
    const LEGACY_DETAILS_KEY = 'forminator_forms_feed_details';
    const LEGACY_STATUS_KEY  = 'frmntr_feed_status';

    const MIGRATED_OPTION = 'gs_formntr_pro_feeds_migrated';

    /**
     * Fully-qualified feeds table name.
     *
     * @since 2.1.0
     */
    public static function table()
    {
        global $wpdb;
        return $wpdb->prefix . 'forminatorgs_feeds';
    }

    /**
     * Fully-qualified feedmeta table name.
     *
     * @since 2.1.0
     */
    public static function meta_table()
    {
        global $wpdb;
        return $wpdb->prefix . 'forminatorgs_feedmeta';
    }

    /**
     * True when the custom tables exist. Positive result cached per request.
     *
     * @since 2.1.0
     */
    public static function tables_ready()
    {
        static $ready = false;
        if ($ready) {
            return true;
        }
        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off schema probe.
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        $ready = ($found === $table);
        return $ready;
    }

    /**
     * Create / upgrade the custom tables (idempotent, dbDelta-safe).
     *
     * @since 2.1.0
     */
    public static function install()
    {
        global $wpdb;

        $charset  = $wpdb->get_charset_collate();
        $feeds    = self::table();
        $feedmeta = self::meta_table();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta(
            "CREATE TABLE {$feeds} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                form_id bigint(20) unsigned NOT NULL,
                feed_name varchar(255) DEFAULT NULL,
                status tinyint(1) NOT NULL DEFAULT 1,
                is_default tinyint(1) NOT NULL DEFAULT 0,
                legacy_meta_id bigint(20) unsigned DEFAULT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY form_id (form_id),
                KEY legacy_meta_id (legacy_meta_id)
            ) {$charset};"
        );

        dbDelta(
            "CREATE TABLE {$feedmeta} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                form_id bigint(20) unsigned NOT NULL,
                feed_id bigint(20) unsigned NOT NULL,
                feed_data longtext NOT NULL,
                created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                UNIQUE KEY feed_id (feed_id),
                KEY form_id (form_id)
            ) {$charset};"
        );
    }

    /**
     * Drop the custom tables. Only from uninstall when the user opted in.
     *
     * @since 2.1.0
     */
    public static function uninstall()
    {
        global $wpdb;
        $feeds    = self::table();
        $feedmeta = self::meta_table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- one-time DROP TABLE during opt-in uninstall; DDL is not cacheable and $feedmeta is $wpdb->prefix plus a hard-coded literal (identifiers cannot use prepare()).
        $wpdb->query("DROP TABLE IF EXISTS {$feedmeta}");
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- one-time DROP TABLE during opt-in uninstall; DDL is not cacheable and $feeds is $wpdb->prefix plus a hard-coded literal (identifiers cannot use prepare()).
        $wpdb->query("DROP TABLE IF EXISTS {$feeds}");
    }

    /* --------------------------------------------------------------------- */
    /*  Reads (custom tables only)                                            */
    /* --------------------------------------------------------------------- */

    /**
     * Fetch a single feed by id. Also resolves a legacy postmeta meta_id via
     * the legacy_meta_id column so old bookmarked URLs keep working.
     *
     * @since 2.1.0
     * @param int $feed_id
     * @return array|null Normalised feed row (with 'data'), or null.
     */
    public static function get($feed_id)
    {
        $feed_id = (int) $feed_id;
        if ($feed_id <= 0 || !self::tables_ready()) {
            return null;
        }

        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom table lookup by PK; value bound via %d, $table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $feed_id),// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );
        if (!$row) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- resolve an old ?feed_id=<meta_id> link; value bound via %d, $table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
            $row = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE legacy_meta_id = %d", $feed_id),// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                ARRAY_A
            );
        }
        if (!$row) {
            return null;
        }

        $feed = self::normalise_row($row);
        $feed['data'] = self::get_data($feed['id']);
        return $feed;
    }

    /**
     * All feeds for one form.
     *
     * @since 2.1.0
     * @param int $form_id
     * @return array[] Normalised feed rows (each with 'data').
     */
    public static function get_for_form($form_id)
    {
        $form_id = (int) $form_id;
        if ($form_id <= 0 || !self::tables_ready()) {
            return array();
        }

        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom table read; value bound via %d, $table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $rows = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$table} WHERE form_id = %d ORDER BY id", $form_id),// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            ARRAY_A
        );

        return self::attach_data(array_map(array(__CLASS__, 'normalise_row'), (array) $rows));
    }

    /**
     * Every feed on the site.
     *
     * @since 2.1.0
     * @return array[] Normalised feed rows (each with 'data').
     */
    public static function all()
    {
        if (!self::tables_ready()) {
            return array();
        }

        global $wpdb;
        $table = self::table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- full custom table read, no parameters; $table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $rows = $wpdb->get_results("SELECT * FROM {$table} ORDER BY id", ARRAY_A);
        return self::attach_data(array_map(array(__CLASS__, 'normalise_row'), (array) $rows));
    }

    /**
     * The decoded feed_data array for one feed id.
     *
     * @since 2.1.0
     * @param int $feed_id feeds.id
     * @return array
     */
    public static function get_data($feed_id)
    {
        $feed_id = (int) $feed_id;
        if ($feed_id <= 0 || !self::tables_ready()) {
            return array();
        }
        global $wpdb;
        $meta_table = self::meta_table();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- custom table read; value bound via %d, $meta_table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $json = $wpdb->get_var(
            $wpdb->prepare("SELECT feed_data FROM {$meta_table} WHERE feed_id = %d", $feed_id)// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );
        if (!$json) {
            return array();
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : array();
    }

    /**
     * Does a feed with this name already exist on the form?
     *
     * @since 2.1.0
     * @param int    $form_id
     * @param string $feed_name
     * @param int    $exclude_id Feed id to skip (for rename checks).
     * @return bool
     */
    public static function name_exists($form_id, $feed_name, $exclude_id = 0)
    {
        $exclude_id = (int) $exclude_id;
        foreach (self::get_for_form($form_id) as $feed) {
            if ((int) $feed['id'] === $exclude_id) {
                continue;
            }
            if (strtolower((string) $feed['feed_name']) === strtolower((string) $feed_name)) {
                return true;
            }
        }
        return false;
    }

    /* --------------------------------------------------------------------- */
    /*  Writes (custom tables only)                                           */
    /* --------------------------------------------------------------------- */

    /**
     * Create a feed.
     *
     * @since 2.1.0
     * @param int    $form_id
     * @param string $feed_name
     * @return int|false New feed id, or false on failure.
     */
    public static function create($form_id, $feed_name)
    {
        $form_id   = (int) $form_id;
        $feed_name = (string) $feed_name;
        if ($form_id <= 0 || !self::tables_ready()) {
            return false;
        }

        global $wpdb;

        // created_at / updated_at are filled by the column DEFAULT CURRENT_TIMESTAMP.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- writing to a plugin-owned custom table; $wpdb->insert() is the sanctioned escaped write API, no core alternative.
        $inserted = $wpdb->insert(self::table(), array(
            'form_id'    => $form_id,
            'feed_name'  => $feed_name,
            'status'     => 1,
            'is_default' => 0,
            'created_at' => current_time('mysql'), // WordPress timezone-aware
            'updated_at' => current_time('mysql'),
        ));

        return $inserted ? (int) $wpdb->insert_id : false;
    }

    /**
     * Rename a feed.
     *
     * @since 2.1.0
     * @param int    $feed_id
     * @param string $feed_name
     * @return bool
     */
    public static function rename($feed_id, $feed_name)
    {
        $feed = self::get($feed_id);
        if (!$feed) {
            return false;
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- updating a plugin-owned custom table; $wpdb->update() is the sanctioned escaped write API, no core alternative and no feed cache to invalidate.
        $wpdb->update(
            self::table(),
            array('feed_name' => (string) $feed_name, 'updated_at' => current_time('mysql')),
            array('id' => $feed['id'])
        );

        return true;
    }

    /**
     * Merge new keys into a feed's feed_data blob.
     *
     * @since 2.1.0
     * @param int   $feed_id
     * @param array $data Keys to set/overwrite (e.g. sheet_id, tab_id, ...).
     * @return bool
     */
    public static function save_data($feed_id, array $data)
    {
        $feed = self::get($feed_id);
        if (!$feed) {
            return false;
        }

        global $wpdb;
        $id         = (int) $feed['id'];
        $now        = current_time('mysql');
        $meta_table = self::meta_table();
        $json       = wp_json_encode(array_merge(self::get_data($id), $data));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter -- existence check before upsert; value bound via %d, $meta_table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM {$meta_table} WHERE feed_id = %d", $id)// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        );

        if ($exists) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- updating a plugin-owned custom table; $wpdb->update() is the sanctioned escaped write API, no core alternative and no feed cache to invalidate.
            $wpdb->update(
                self::meta_table(),
                array('feed_data' => $json, 'updated_at' => $now),
                array('feed_id' => $id)
            );
        } else {
            // created_at / updated_at are filled by the column DEFAULT CURRENT_TIMESTAMP.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- writing to a plugin-owned custom table; $wpdb->insert() is the sanctioned escaped write API, no core alternative.
            $wpdb->insert(self::meta_table(), array(
                'form_id'   => (int) $feed['form_id'],
                'feed_id'   => $id,
                'feed_data' => $json,
            ));
        }

        return true;
    }

    /**
     * Enable/disable a feed.
     *
     * @since 2.1.0
     * @param int  $feed_id
     * @param bool $active
     * @return bool
     */
    public static function set_status($feed_id, $active)
    {
        $feed = self::get($feed_id);
        if (!$feed) {
            return false;
        }

        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- updating a plugin-owned custom table; $wpdb->update() is the sanctioned escaped write API, no core alternative and no feed cache to invalidate.
        $wpdb->update(
            self::table(),
            array('status' => $active ? 1 : 0, 'updated_at' => current_time('mysql')),
            array('id' => $feed['id'])
        );

        return true;
    }

    /**
     * Delete a feed and its feed_data row.
     *
     * @since 2.1.0
     * @param int $feed_id
     * @return bool
     */
    public static function delete($feed_id)
    {
        $feed = self::get($feed_id);
        if (!$feed) {
            return false;
        }

        global $wpdb;
        $id = (int) $feed['id'];
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- deleting from a plugin-owned custom table; $wpdb->delete() is the sanctioned escaped API, no core alternative and no feed cache to invalidate.
        $wpdb->delete(self::meta_table(), array('feed_id' => $id));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- deleting from a plugin-owned custom table; $wpdb->delete() is the sanctioned escaped API, no core alternative and no feed cache to invalidate.
        $wpdb->delete(self::table(), array('id' => $id));

        return true;
    }

    /* --------------------------------------------------------------------- */
    /*  Internal helpers                                                      */
    /* --------------------------------------------------------------------- */

    /**
     * Cast a raw feeds row into the normalised shape (without 'data').
     *
     * @since 2.1.0
     */
    private static function normalise_row($row)
    {
        return array(
            'id'             => (int) $row['id'],
            'form_id'        => (int) $row['form_id'],
            'feed_name'      => (string) $row['feed_name'],
            'status'         => isset($row['status']) ? (int) $row['status'] : 1,
            'is_default'     => isset($row['is_default']) ? (int) $row['is_default'] : 0,
            'legacy_meta_id' => empty($row['legacy_meta_id']) ? null : (int) $row['legacy_meta_id'],
            'created_at'     => isset($row['created_at']) ? (string) $row['created_at'] : '',
            'updated_at'     => isset($row['updated_at']) ? (string) $row['updated_at'] : '',
        );
    }

    /**
     * Bulk-load feed_data for a list of normalised rows.
     *
     * @since 2.1.0
     */
    private static function attach_data(array $feeds)
    {
        if (empty($feeds)) {
            return array();
        }

        global $wpdb;
        $meta_table   = self::meta_table();
        $ids          = array_map('intval', wp_list_pluck($feeds, 'id'));
        $placeholders = implode(', ', array_fill(0, count($ids), '%d'));
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare, PluginCheck.Security.DirectDB.UnescapedDBParameter -- IN() list: $placeholders is a generated %d list bound from the all-integer $ids array; $meta_table is $wpdb->prefix + a hard-coded literal (identifiers cannot use prepare()).
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT feed_id, feed_data FROM {$meta_table} WHERE feed_id IN ($placeholders)",// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
                $ids
            ),
            ARRAY_A
        );

        $map = array();
        foreach ((array) $rows as $r) {
            $decoded = json_decode($r['feed_data'], true);
            $map[(int) $r['feed_id']] = is_array($decoded) ? $decoded : array();
        }

        foreach ($feeds as &$feed) {
            $feed['data'] = isset($map[$feed['id']]) ? $map[$feed['id']] : array();
        }
        unset($feed);

        return $feeds;
    }

    /* --------------------------------------------------------------------- */
    /*  One-time migration: postmeta  ->  custom tables                       */
    /* --------------------------------------------------------------------- */

    /**
     * Copy pre-existing postmeta feeds into the custom tables.
     *
     * Idempotent, batched, and safe to call repeatedly - driven by migrate_all()
     * from run_on_upgrade() / activation. Reads postmeta ONLY here; the rest of
     * the plugin never touches it.
     *
     * @since 2.1.0
     * @param int $batch Rows per pass.
     * @return bool True when there is nothing left to migrate.
     */
    public static function migrate($batch = 200)
    {
        if (get_option(self::MIGRATED_OPTION) === 'done') {
            return true;
        }

        global $wpdb;
        $feeds_table = self::table();

        // Ensure the tables exist AND carry the columns this routine needs
        // (a site may have pre-created bare tables by hand).
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema probe for a column.
        $has_legacy_col = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = %s AND COLUMN_NAME = 'legacy_meta_id'",
                $feeds_table
            )
        );
        if (!self::tables_ready() || !$has_legacy_col) {
            self::install();
        }

        // Legacy feed rows that do not yet have a mirror row.
        // $feeds_table is $wpdb->prefix + a hard-coded literal (identifiers cannot be bound); values use %s / %d.
        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $rows = $wpdb->get_results(// phpcs:ignore PluginCheck.Security.DirectDB.UnescapedDBParameter
            $wpdb->prepare(
                "SELECT pm.meta_id, pm.post_id, pm.meta_value
                   FROM {$wpdb->postmeta} pm
                   LEFT JOIN {$feeds_table} f ON f.legacy_meta_id = pm.meta_id
                  WHERE pm.meta_key = %s AND f.id IS NULL
                  ORDER BY pm.meta_id
                  LIMIT %d",
                self::LEGACY_FEED_KEY,
                (int) $batch
            ),
            ARRAY_A
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // Query failed (schema not ready) - do NOT mark complete; retry next tick.
        if ($wpdb->last_error) {
            return false;
        }

        if (empty($rows)) {
            update_option(self::MIGRATED_OPTION, 'done', false);
            return true;
        }

        foreach ($rows as $r) {
            $value = maybe_unserialize($r['meta_value']);
            if (!is_array($value)) {
                continue;
            }

            $status = get_post_meta($r['meta_id'], self::LEGACY_STATUS_KEY, true);

            // created_at / updated_at are filled by the column DEFAULT CURRENT_TIMESTAMP.
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- one-time migration write to a plugin-owned custom table; $wpdb->insert() is the sanctioned escaped write API.
            $wpdb->insert($feeds_table, array(
                'form_id'        => (int) $r['post_id'],
                'feed_name'      => isset($value['feed_name']) ? (string) $value['feed_name'] : '',
                'status'         => ($status === '' || $status === false) ? 1 : (int) $status,
                'is_default'     => 0,
                'legacy_meta_id' => (int) $r['meta_id'],
            ));
            $new_id = (int) $wpdb->insert_id;
            if ($new_id <= 0) {
                continue;
            }

            $details = get_post_meta($r['meta_id'], self::LEGACY_DETAILS_KEY, true);
            if (is_array($details) && !empty($details)) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- one-time migration write to a plugin-owned custom table; $wpdb->insert() is the sanctioned escaped write API.
                $wpdb->insert(self::meta_table(), array(
                    'form_id'   => (int) $r['post_id'],
                    'feed_id'   => $new_id,
                    'feed_data' => wp_json_encode($details),
                ));
            }
        }

        // More rows may remain - migrate_all() loops, and run_on_upgrade()
        // re-runs this on the next admin request until it reports done.
        return false;
    }

    /**
     * Run migrate() repeatedly until it reports done (bounded).
     *
     * @since 2.1.0
     * @return bool True when migration is complete.
     */
    public static function migrate_all()
    {
        $guard = 0;
        while (self::migrate() === false && $guard < 500) {
            $guard++;
        }
        return get_option(self::MIGRATED_OPTION) === 'done';
    }

    /**
     * Delete the old postmeta feed rows after migration. NOT run automatically -
     * call once you are confident the custom tables hold everything. Opt in with
     * add_filter('gs_formntr_purge_legacy_feeds', '__return_true') then trigger,
     * or call GS_FORMNTR_Feed_Store::purge_legacy() directly.
     *
     * @since 2.1.0
     * @return int Rows removed.
     */
    public static function purge_legacy()
    {
        global $wpdb;

        // meta_ids of the feed rows.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one-off cleanup.
        $feed_mids = $wpdb->get_col(
            $wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = %s", self::LEGACY_FEED_KEY)
        );

        $removed = 0;
        foreach ((array) $feed_mids as $mid) {
            // details + status rows are keyed by post_id = the feed's meta_id.
            $removed += (int) $wpdb->delete($wpdb->postmeta, array('post_id' => (int) $mid, 'meta_key' => self::LEGACY_DETAILS_KEY));// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.DirectDatabaseQuery.NoCaching
            $removed += (int) $wpdb->delete($wpdb->postmeta, array('post_id' => (int) $mid, 'meta_key' => self::LEGACY_STATUS_KEY));// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.SlowDBQuery.slow_db_query_meta_key, WordPress.DB.DirectDatabaseQuery.NoCaching
            $removed += (int) delete_metadata_by_mid('post', $mid);
        }

        return $removed;
    }
}
