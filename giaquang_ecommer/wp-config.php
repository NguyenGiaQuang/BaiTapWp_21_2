<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'giaquang_ecommer' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '<**I{oTMSx&q_4Kf@(|v8o]K{, q{AmR+YFYqw&vP&9/>}]K77*0n)oD,`X1_p-^' );
define( 'SECURE_AUTH_KEY',  'GQb8}qE^,nq;5;QhyO:!N$*`0M~)GX3NY9@.&x1ml20?+)!*>Bn@0F^5Q.QLfb0e' );
define( 'LOGGED_IN_KEY',    '/tT#c~.PP4~,S:7Dj&$Z[!6Q:SIWxW1{}fqSPS(,wOy9q0c0eZ3j+<6VlxPX/d0S' );
define( 'NONCE_KEY',        'o4N5O_+2R>OR;Z-JWY=7/sN`[s6.Z$$mL$-I)w:$,_W.Yo.TU8+xr,bG>Pnh(68E' );
define( 'AUTH_SALT',        '8=.^h.cgd+iyJ3=;^<+. g0&a&oKS&k54XKIyg!)IyrQv+;3l<waP4%8X^r|zE&a' );
define( 'SECURE_AUTH_SALT', '0M0}k4nh+;_wRh=n{|uE)VPzUr11.PiAw`!zhNrIuogjeF5&<K7VjeEsK4lz<NRf' );
define( 'LOGGED_IN_SALT',   '?3ukp1%v]-:).T}3J}|kBp89y>RQ(%o]:w5)l tWwL0$&2@ZW1D8u% 7><}}FmmV' );
define( 'NONCE_SALT',       '{goH6Rl24f$M38{0nhoTTOeJ=*C1)t]{YU3tBC8MASJx1P<-s7}u;F?A-s?iM]0?' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
