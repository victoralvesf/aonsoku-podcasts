<?php

namespace App\Helpers;

enum FilterType: string
{
    case Episode = 'episode';
    case Podcast = 'podcast';
}

class FilterHelper
{
    protected $defaults = [
        'per_page' => 20,
        'sort' => 'desc',
        'order_by' => 'published_at',
        'filter_by' => 'both',
    ];

    protected $podcast = [
        'sort' => 'asc',
        'order_by' => 'title',
    ];

    protected $filters;

    public function __construct(array $filters, FilterType $type = FilterType::Episode)
    {
        if ($type == FilterType::Podcast) {
            $this->defaults = array_merge($this->defaults, $this->podcast);
        }

        $this->filters = array_merge($this->defaults, $filters);
    }

    public function getPerPage(): int
    {
        return intval($this->filters['per_page']);
    }

    public function getSort(): string
    {
        return $this->filters['sort'];
    }

    public function getOrderBy(): string
    {
        return $this->filters['order_by'];
    }

    public function getFilterBy(): string
    {
        return $this->filters['filter_by'];
    }

    public function getSearchQuery(): string
    {
        return isset($this->filters['query']) ? "%{$this->filters['query']}%" : '%';
    }
}
