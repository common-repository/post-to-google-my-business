<?php
/**
 * @license MIT
 *
 * Modified by __root__ on 28-October-2024 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */

namespace PGMB\Vendor\Rarst\WordPress\DateTime;

/**
 * Extension of DateTime for WordPress.
 */
class WpDateTime extends \DateTime implements WpDateTimeInterface {

	const MYSQL = 'Y-m-d H:i:s';

	use WpDateTimeTrait;
}
