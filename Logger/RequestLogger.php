<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/2/19 9:37 AM
 */

namespace Alamirault\ElasticsearchBundle\Logger;


use Psr\Log\LoggerInterface;

class RequestLogger
{
    private $calls = [];
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RequestLogger constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function logSearch(string $route, string $method, string $duration, string $statusCode, array $response, $data)
    {
        $this->calls[] = [
            'route' => $route,
            'method' => $method,
            'duration' => $duration,
            'statusCode' => $statusCode,
            'response' => self::json($response),
            'data' => \is_array($data) ? self::json($data) : $data,
        ];
    }

    private static function json($json)
    {
        if (is_object($json) || is_array($json)) {
            $json = json_encode($json);
        }
        $result = '';
        $level = 0;
        $prev_char = '';
        $in_quotes = false;
        $ends_line_level = null;
        $json_length = strlen($json);
        for ($i = 0; $i < $json_length; $i++) {
            $char = $json[$i];
            $new_line_level = null;
            $post = "";
            if ($ends_line_level !== null) {
                $new_line_level = $ends_line_level;
                $ends_line_level = null;
            }
            if ($char === '"' && $prev_char != '\\') {
                $in_quotes = !$in_quotes;
            } elseif (!$in_quotes) {
                switch ($char) {
                    case '}':
                    case ']':
                        $level--;
                        $ends_line_level = null;
                        $new_line_level = $level;
                        break;
                    case '{':
                    case '[':
                        $level++;
                        $ends_line_level = $level;
                        break;
                    case ',':
                        $ends_line_level = $level;
                        break;
                    case ':':
                        $post = " ";
                        break;
                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = null;
                        break;
                }
            }
            if ($new_line_level !== null) {
                $result .= "\n" . str_repeat("  ", $new_line_level);
            }
            $result .= $char . $post;
            $prev_char = $char;
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getCalls()
    {
        return $this->calls;
    }
}