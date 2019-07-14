<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 7/1/19 11:15 AM
 */

namespace Alamirault\ElasticsearchBundle\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class ItemsPerPage
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * ItemsPerPage constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getValue(): int
    {
        $request = $this->requestStack->getMasterRequest();

        $itemsPerPage = 20;
        $itemsPerPageCookie = $request->cookies->getInt('itemsPerPage');

        if (!empty($itemsPerPageCookie)) {
            $itemsPerPage = $itemsPerPageCookie;
        }

        return $request->query->getInt('nbItemPerPage', $itemsPerPage);
    }
}