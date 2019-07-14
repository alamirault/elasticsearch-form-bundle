<?php
/**
 * User: Antoine LAMIRAULT <lamiraultantoine@gmail.com>
 * Date: 6/28/19 2:21 PM
 */

namespace Alamirault\ElasticsearchBundle\Model;


use Elastica\ResultSet;

class SortableItems
{
    /**
     * @var array
     */
    private $items;
    /**
     * @var string
     */
    private $sortField;
    /**
     * @var string
     */
    private $sortDirection;
    /**
     * @var int
     */
    private $currentPage;


    /**
     * SortableItems constructor.
     * @param ResultSet $items
     * @param string $sortField
     * @param string $sortDirection
     * @param int $currentPage
     */
    public function __construct(?ResultSet $items, string $sortField, string $sortDirection, int $currentPage)
    {
        $this->items = $items;
        $this->sortField = $sortField;
        $this->sortDirection = $sortDirection;
        $this->currentPage = $currentPage;
    }

    /**
     * @return ResultSet
     */
    public function getItems(): ?ResultSet
    {
        return $this->items;
    }

    /**
     * @param ResultSet $items
     */
    public function setItems(ResultSet $items)
    {
        $this->items = $items;
    }

    /**
     * @return string
     */
    public function getSortField(): string
    {
        return $this->sortField;
    }

    /**
     * @param string $sortField
     */
    public function setSortField(string $sortField)
    {
        $this->sortField = $sortField;
    }

    /**
     * @return string
     */
    public function getSortDirection(): string
    {
        return $this->sortDirection;
    }

    /**
     * @param string $sortDirection
     */
    public function setSortDirection(string $sortDirection)
    {
        $this->sortDirection = $sortDirection;
    }

    public function isSortedBy(string $name)
    {
        return $name === $this->sortField;
    }

    /**
     * @return int
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }
}