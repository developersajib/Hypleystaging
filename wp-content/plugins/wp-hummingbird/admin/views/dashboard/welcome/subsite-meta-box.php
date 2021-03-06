<?php
/**
 * Dashboard summary meta box.
 *
 * @since 2.0.0
 * @package Hummingbird
 *
 * @var bool   $caching_enabled   Page caching status.
 * @var bool   $database_items    Available items to purge in Advanced Tools.
 * @var bool   $is_doing_report   If is doing performance report.
 * @var object $last_report       Last report object.
 * @var bool   $minify_enabled    Asset optimization status.
 * @var bool   $report_dismissed  Last report dismissed warning.
 * @var string $report_type        Performance report type: desktop or mobile.
 */

use Hummingbird\Core\Modules\Performance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$branded_image = apply_filters( 'wpmudev_branding_hero_image', '' );
?>

<?php if ( $branded_image ) : ?>
	<div class="sui-summary-image-space" aria-hidden="true" style="background-image: url('<?php echo esc_url( $branded_image ); ?>')"></div>
<?php else : ?>
	<div class="sui-summary-image-space" aria-hidden="true"></div>
<?php endif; ?>
<div class="sui-summary-segment">
	<div class="sui-summary-details">
		<?php if ( $last_report && ! is_wp_error( $last_report ) && ! $report_dismissed ) : ?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->{$report_type}->score ); ?></span>
			<span aria-hidden="true" class="sui-icon-<?php echo esc_attr( Performance::get_impact_class( $last_report->{$report_type}->score, 'icon' ) ); ?> sui-md sui-<?php echo esc_attr( Performance::get_impact_class( $last_report->{$report_type}->score ) ); ?>"></span>
			<span class='sui-summary-percent'>/100</span>
		<?php elseif ( $is_doing_report ) : ?>
			<div class="sui-progress-text sui-icon-loader sui-loading"></div>
		<?php elseif ( $report_dismissed && isset( $last_report->{$report_type}->score ) ) : ?>
			<span class="sui-summary-large"><?php echo esc_html( $last_report->{$report_type}->score ); ?></span>
			<span aria-hidden="true" class="sui-icon-info sui-md"></span>
			<span class='sui-summary-percent'>/100</span>
		<?php else : ?>
			&mdash;
		<?php endif; ?>
		<span class="sui-summary-sub"><?php esc_html_e( 'Performance score', 'wphb' ); ?></span>

		<span class="sui-summary-detail">
			<?php
			if ( $last_report && ! is_wp_error( $last_report ) ) {
				$data_time    = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $last_report->time ) ) );
				$time_string  = esc_html( date_i18n( get_option( 'date_format' ), $data_time ) );
				$time_string .= sprintf(
					/* translators: %s - time in proper format */
					esc_html_x( ' at %s', 'Time of the last performance report', 'wphb' ),
					esc_html( date_i18n( get_option( 'time_format' ), $data_time ) )
				);
				echo esc_html( $time_string );
			} elseif ( $is_doing_report ) {
				$time_string = esc_html__( 'Running scan...', 'wphb' );
			} else {
				$time_string = esc_html__( 'Never', 'wphb' );
			}
			?>
		</span>
		<span class="sui-summary-sub"><?php esc_html_e( 'Last test date', 'wphb' ); ?></span>
	</div>
</div>
<div class="sui-summary-segment">
	<ul class="sui-list">
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Page Caching', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<span class="<?php echo $caching_enabled ? 'sui-tag sui-tag-success' : 'sui-tag'; ?>">
					<?php $caching_enabled ? esc_html_e( 'Active', 'wphb' ) : esc_html_e( 'Inactive', 'wphb' ); ?>
				</span>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Asset Optimization', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<span class="<?php echo $minify_enabled ? 'sui-tag sui-tag-success' : 'sui-tag'; ?>">
					<?php $minify_enabled ? esc_html_e( 'Active', 'wphb' ) : esc_html_e( 'Inactive', 'wphb' ); ?>
				</span>
			</span>
		</li>
		<li>
			<span class="sui-list-label"><?php esc_html_e( 'Database Cleanup', 'wphb' ); ?></span>
			<span class="sui-list-detail">
				<?php
				printf(
					/* translators: %d - number of entries */
					esc_html__( '%d dispensable entries', 'wphb' ),
					absint( $database_items )
				)
				?>
			</span>
		</li>
	</ul>
</div>