<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/2/19 11:52 AM
 */

namespace Alamirault\ElasticsearchBundle\DataCollector;

use Alamirault\ElasticsearchBundle\Logger\RequestLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class RequestCollector extends DataCollector
{
    /**
     * @var RequestLogger
     */
    private $requestLogger;

    public function __construct(RequestLogger $requestLogger)
    {
        $this->requestLogger = $requestLogger;
    }


    /**
     * Collects data for the given Request and Response.
     * @param Request $request
     * @param Response $response
     * @param \Exception|null $exception
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data = [
            'calls' => $this->requestLogger->getCalls(),
        ];
    }

    public function getRequestCount(): int
    {
        return count($this->getRequests());
    }

    public function getRequests()
    {
        return $this->data["calls"] ?? [];
    }

    public function getTime(): int
    {
        $time = 0;
        foreach ($this->getRequests() as $call) {
            $time += $call['duration'];
        }

        return $time;
    }

    public function getErroredCount(): int
    {
        $sum = 0;
        foreach ($this->getRequests() as $call) {
            if ($this->isError((string)$call['statusCode'])) {
                $sum++;
            }
        }
        return $sum;
    }

    public function isError(string $httpCode): bool
    {
        $httpCodeType = $httpCode[0];
        if ($httpCodeType === "4" || $httpCodeType === "5") {
            return true;
        }
        return false;
    }

    public function reset()
    {
        $this->data = [];
    }

    /**
     * Returns the name of the collector.
     *
     * @return string The collector name
     */
    public function getName()
    {
        return 'app.elasticsearch_request_collector';
    }
}