<?php
if (!defined('ABSPATH')) {
  exit;
}

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Logging class
 *
 * @author Mintun Media <hello@mintunmedia.com>
 * @since 10/29/22
 */
class MM_Logger {

  private static $instance;

  /**
   * Returns an instance of this class
   *
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public static function get_instance() {
    if (null == self::$instance) {
      self::$instance = new MM_Logger();
    }
    return self::$instance;
  }

  /**
   * Constructor
   *
   * @author Mintun Media <hello@mintunmedia.com>
   */
  public function __construct() {
  }

  /**
   * Write logo to MM Logger
   *
   * Usage: MM_Logger::log()
   *
   * @param  string $string    Main message to send to output.
   * @param  array  $array     Output an array in JSON
   * @param  bool   $first_log Adds astericks to beginning of output
   * @param  string $level     debug, info, warning, error. Defaults to info
   * @return void
   *
   * @author Mintun Media <hello@mintunmedia.com>
   * @docs https://github.com/Seldaek/monolog
   */
  public static function log($string = '', $array = [], $first_log = false, $level = 'info') {
    $stream_logger = new RotatingFileHandler(WP_CONTENT_DIR . '/mmlogs/debug.log', 30, Logger::DEBUG);
    $dateFormat = "m-d-Y H:i:s";
    $output = "%datetime% | %level_name% | %message% %context% %extra%" . PHP_EOL;
    $formatter = new LineFormatter($output, $dateFormat, true, true);

    $stream_logger->setFormatter($formatter);
    $logger = new Logger('mm_plugin_logger');
    $logger->pushHandler($stream_logger);

    // First Log Handler - Breaks up the logs so it's easier to read
    if ($first_log) {
      $logger->$level('');
      $logger->$level('**************');
    }

    if (!empty($array)) {
      // Array + String
      if (is_object($array)) {
        // Convert Object to Array
        $array = json_decode(json_encode($array), true);
      }
      $logger->$level($string);
      $logger->$level(print_r($array, true));
    } else {
      // String Only
      $logger->$level($string);
    }
  }
}
