<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'almaconsciente');

/** MySQL database username */
define('DB_USER', 'homestead');

/** MySQL database password */
define('DB_PASSWORD', 'secret');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '#-~_w8T6n#ctuK+~XxzLsbvTkBp@gpl`_6qtNs0Y@4es#M9#M5hGD?ziB&sRJVp<');
define('SECURE_AUTH_KEY',  '?ujVi_.nH;@ xeS/]L0^[U[R5:u?XnA-kN*jAAvuY_.nSeVX];L`(iPyZv57)8Cx');
define('LOGGED_IN_KEY',    '!R2+nWHm08&$$Osb8_6C:i@O9KCv=rC#r(hr7HAyDoUBOu1%e8#HBq0?:mi0R!mk');
define('NONCE_KEY',        'T<yu0XB8K|H!$3w~nMUkFAQ9$(qQM,%6sUTDW![[_(4ioOYC6x=r,O9iC~.qK`#q');
define('AUTH_SALT',        '6c57A#oMW$J:X^QYcuZbz&Uul#V5LR#h`otSRt:$M/ik%;|}dt/q5XX[L}yr_R+R');
define('SECURE_AUTH_SALT', '05@IRIcmb_;XZ*N;c*}SLZLJuw@G(sXFZ>U{cYob|1TCWV9V+rXnLTR7c?+ HP*|');
define('LOGGED_IN_SALT',   'a<mPTB#80IN{2oD59E@;9]~~9!+0m3+m0ki9Qu9 U+d#`luG]y,fvE[q)e|.T :-');
define('NONCE_SALT',       'B|ZkCS<}(>/0ov#&{Rv(M~7j6TX0R> x>_E8ao|1:VHsb:mu8dyld8,&>?M0>p`8');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_alma_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
