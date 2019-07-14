<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/2/19 9:48 AM
 */

namespace Alamirault\ElasticsearchBundle\Service;


use Alamirault\ElasticsearchBundle\Logger\RequestLogger;
use Elastica\Client;
use Elastica\Exception\ConnectionException;
use Elastica\Request;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ElasticsearchClient extends Client
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var RequestLogger
     */
    private $requestLogger;
    /**
     * @var int
     */
    private $maxResultWindow;

    /**
     * ElasticsearchClient constructor.
     * @param array $config
     * @param $callback
     * @param LoggerInterface $logger
     * @param SerializerInterface $serializer
     * @param RequestLogger $requestLogger
     * @param int $maxResultWindow
     */
    public function __construct(array $config, $callback, LoggerInterface $logger, SerializerInterface $serializer, RequestLogger $requestLogger, int $maxResultWindow)
    {
        $this->serializer = $serializer;
        parent::__construct($config, $callback, $logger);
        $this->requestLogger = $requestLogger;
        $this->maxResultWindow = $maxResultWindow;
    }

    protected function _log($context)
    {
        if ($context instanceof ConnectionException) {
            $this->_logger->error('>> ELASTICSEARCH ERROR', [
                'exception' => $context,
                'request' => $context->getRequest()->toArray(),
                'retry' => $this->hasConnection(),
            ]);

            return;
        }

        if ($context instanceof Request) {
            $queryTime = "N.A";
            $status = "N.A";
            $response = [];
            if ($this->_lastResponse) {
                $queryTime = $this->_lastResponse->getQueryTime();
                $status = $this->_lastResponse->getStatus();
                $response = $this->_lastResponse->getData();
            }

            $this->requestLogger->logSearch($context->getPath(), $context->getMethod(), $queryTime, $status, $response, $context->getData());
            $data = [
                'request' => $context->toArray(),
                'responseStatus' => $this->_lastResponse ? $this->_lastResponse->getStatus() : null,
                'queryTime' => $this->_lastResponse ? $this->_lastResponse->getQueryTime() : null,
            ];
            $dataSerialized = $this->serializer->serialize($data, "json");

            $this->_logger->info('>> ELASTICSEARCH REQUEST: ' . $dataSerialized);

            return;
        }

        $this->_logger->debug('Response: ', [
            'message' => $context,
        ]);
    }

    /**
     * @return int
     */
    public function getMaxResultWindow(): int
    {
        return $this->maxResultWindow;
    }
}