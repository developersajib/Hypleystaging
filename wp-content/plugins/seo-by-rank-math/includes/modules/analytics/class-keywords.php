<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Keywords class.
 */
class Keywords extends Posts {

	/**
	 * Get most recent day's keywords.
	 *
	 * @return array
	 */
	public function get_recent_keywords() {
		global $wpdb;

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT query
			FROM {$wpdb->prefix}rank_math_analytics_gsc
			WHERE DATE(created) = (SELECT MAX(DATE(created)) FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE created BETWEEN %s AND %s)
			GROUP BY query",
			Stats::get()->start_date,
			Stats::get()->end_date
		);
		$data = $wpdb->get_results( $query );
		// phpcs:enable

		return $data;
	}

	/**
	 * Get keywords.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_keywords_rows( WP_REST_Request $request ) {
		$per_page = 25;
		$offset   = ( $request->get_param( 'page' ) - 1 ) * $per_page;

		// Get most recent day's keywords only.
		$keywords = $this->get_recent_keywords();
		$keywords = wp_list_pluck( $keywords, 'query' );
		$keywords = array_map( 'esc_sql', $keywords );
		$rows     = $this->get_analytics_data(
			[
				'dimension' => 'query',
				'objects'   => false,
				'pageview'  => false,
				'orderBy'   => 'impressions',
				'offset'    => $offset,
				'perpage'   => $per_page,
				'sub_where' => " AND query IN ('" . join( "', '", $keywords ) . "')",
			]
		);

		return apply_filters( 'rank_math/analytics/keywords', $rows );
	}

	/**
	 * Get top 50 keywords.
	 *
	 * @return object
	 */
	public function get_top_keywords() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'top_keywords', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$query = $wpdb->prepare(
			"SELECT COUNT(t1.query) AS total,
				CASE
					WHEN t1.position BETWEEN 1 AND 3 THEN 'top3'
					WHEN t1.position BETWEEN 4 AND 10 THEN 'top10'
					WHEN t1.position BETWEEN 11 AND 50 THEN 'top50'
					WHEN t1.position BETWEEN 51 AND 100 THEN 'top100'
					ELSE 'none'
				END AS position_type
			FROM (SELECT query, ROUND( AVG(position), 0 ) AS position 
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s AND DATE(created) = (SELECT MAX(DATE(created)) FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE created BETWEEN %s AND %s)
				GROUP BY query 
				ORDER BY position) as t1
			GROUP BY position_type",
			$this->start_date,
			$this->end_date,
			$this->start_date,
			$this->end_date
		);
		$data  = $wpdb->get_results( $query ); // phpcs:ignore

		$query   = $wpdb->prepare(
			"SELECT COUNT(t1.query) AS total,
				CASE
					WHEN t1.position BETWEEN 1 AND 3 THEN 'top3'
					WHEN t1.position BETWEEN 4 AND 10 THEN 'top10'
					WHEN t1.position BETWEEN 11 AND 50 THEN 'top50'
					WHEN t1.position BETWEEN 51 AND 100 THEN 'top100'
					ELSE 'none'
				END AS position_type
			FROM (SELECT query, ROUND( AVG(position), 0 ) AS position 
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s AND DATE(created) = (SELECT MAX(DATE(created)) FROM {$wpdb->prefix}rank_math_analytics_gsc WHERE created BETWEEN %s AND %s)
				GROUP BY query 
				ORDER BY position) as t1
			GROUP BY position_type",
			$this->compare_start_date,
			$this->compare_end_date,
			$this->compare_start_date,
			$this->compare_end_date
		);
		$compare = $wpdb->get_results( $query ); // phpcs:ignore

		$positions = [
			'top3'          => [
				'total'      => 0,
				'difference' => 0,
			],
			'top10'         => [
				'total'      => 0,
				'difference' => 0,
			],
			'top50'         => [
				'total'      => 0,
				'difference' => 0,
			],
			'top100'        => [
				'total'      => 0,
				'difference' => 0,
			],
			'ctr'           => 0,
			'ctrDifference' => 0,
		];

		$positions = $this->get_top_position_total( $positions, $data, 'total' );
		$positions = $this->get_top_position_total( $positions, $compare, 'difference' );

		// CTR.
		$positions['ctr'] = DB::analytics()
			->selectAvg( 'ctr', 'ctr' )
			->whereBetween( 'created', [ $this->start_date, $this->end_date ] )
			->getVar();

		$positions['ctrDifference'] = DB::analytics()
			->selectAvg( 'ctr', 'ctr' )
			->whereBetween( 'created', [ $this->compare_start_date, $this->compare_end_date ] )
			->getVar();

		$positions['ctr']           = empty( $positions['ctr'] ) ? 0 : $positions['ctr'];
		$positions['ctrDifference'] = empty( $positions['ctrDifference'] ) ? 0 : $positions['ctrDifference'];
		$positions['ctrDifference'] = $positions['ctr'] - $positions['ctrDifference'];

		set_transient( $cache_key, $positions, DAY_IN_SECONDS );

		return $positions;
	}

	/**
	 * Get position graph
	 *
	 * @return array
	 */
	public function get_top_position_graph() {
		global $wpdb;

		$cache_key = $this->get_cache_key( 'top_keywords_graph', $this->days . 'days' );
		$cache     = get_transient( $cache_key );

		if ( false !== $cache ) {
			return $cache;
		}

		$intervals     = $this->get_intervals();
		$sql_daterange = $this->get_sql_date_intervals( $intervals );

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT MAX(DATE(created)) as date, {$sql_daterange}
			FROM {$wpdb->prefix}rank_math_analytics_gsc
			WHERE created BETWEEN %s AND %s
			GROUP BY range_group",
			$this->start_date,
			$this->end_date
		);
		$position_dates = $wpdb->get_results( $query, ARRAY_A );
		// phpcs:enable

		if ( count( $position_dates ) === 0 ) {
			return [];
		}

		$dates = [];
		foreach ( $position_dates as $row ) {
			array_push( $dates, $row['date'] );
		}
		$dates = '(\'' . join( '\', \'', $dates ) . '\')';

		// phpcs:disable
		$query = $wpdb->prepare(
			"SELECT COUNT(t.query) AS total, t.date, 
				CASE
					WHEN t.position BETWEEN 1 AND 3 THEN 'top3'
					WHEN t.position BETWEEN 4 AND 10 THEN 'top10'
					WHEN t.position BETWEEN 11 AND 50 THEN 'top50'
					WHEN t.position BETWEEN 51 AND 100 THEN 'top100'
					ELSE 'none'
				END AS position_type
			FROM (
				SELECT query, ROUND( AVG(position), 0 ) AS position, Date(created) as date
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE created BETWEEN %s AND %s AND DATE(created) IN {$dates}
				GROUP BY DATE(created), query) AS t
			GROUP BY t.date, position_type",
			$this->start_date,
			$this->end_date
		);
		$position_data = $wpdb->get_results( $query );
		// phpcs:enable

		// Data.
		$data = $this->get_date_array(
			$intervals['dates'],
			[
				'top3'   => 0,
				'top10'  => 0,
				'top50'  => 0,
				'top100' => 0,
			]
		);

		foreach ( $position_data as $row ) {
			if ( ! isset( $intervals['map'][ $row->date ] ) ) {
				continue;
			}

			$date = $intervals['map'][ $row->date ];

			if ( ! isset( $data[ $date ][ $row->position_type ] ) ) {
				continue;
			}

			$key = $row->position_type;

			$data[ $date ][ $key ] = $row->total;
		}

		$data = array_values( $data );
		set_transient( $cache_key, $data, DAY_IN_SECONDS );

		return $data;
	}

	/**
	 * Get graph data.
	 *
	 * @param  string $position Position for which data required.
	 * @param  array  $data     Data array.
	 * @param  array  $map      Interval map.
	 * @return array
	 */
	private function get_postion_graph_data( $position, $data, $map ) {
		global $wpdb;

		$positions = [
			'top3'   => '1 AND 3',
			'top10'  => '4 AND 10',
			'top50'  => '11 AND 50',
			'top100' => '51 AND 100',
		];
		$range     = $positions[ $position ];

		// phpcs:disable
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT( created,'%%Y-%%m-%%d') as date, COUNT(query) as total
				FROM {$wpdb->prefix}rank_math_analytics_gsc
				WHERE position BETWEEN {$range} AND created BETWEEN %s AND %s
				GROUP BY created
				ORDER BY created ASC",
				$this->start_date,
				$this->end_date
			)
		);
		// phpcs:enable

		foreach ( $rows as $row ) {
			if ( ! isset( $map[ $row->date ] ) ) {
				continue;
			}

			$date = $map[ $row->date ];

			$data[ $date ][ $position ][] = absint( $row->total );
		}

		return $data;
	}

	/**
	 * Get top position total.
	 *
	 * @param  array  $positions Position array.
	 * @param  array  $rows      Data to process.
	 * @param  string $where     What data to get total.
	 *
	 * @return array
	 */
	private function get_top_position_total( $positions, $rows, $where ) {
		foreach ( $rows as $row ) {
			$positions[ $row->position_type ][ $where ] = $row->total;
		}

		if ( 'difference' === $where ) {
			$positions['top3']['difference']   = $positions['top3']['total'] - $positions['top3']['difference'];
			$positions['top10']['difference']  = $positions['top10']['total'] - $positions['top10']['difference'];
			$positions['top50']['difference']  = $positions['top50']['total'] - $positions['top50']['difference'];
			$positions['top100']['difference'] = $positions['top100']['total'] - $positions['top100']['difference'];
		}

		return $positions;
	}
}
