<?php

namespace Legeartis\UnifiedSearch\responseObjects;

class VehicleContext extends Base
{
    /** @var bool */
    protected $indexed = false;

    /** @var int */
    protected $indexedAutoId;

    /** @var string */
    protected $vin;

    private const map = [
        'indexed' => 'bool',
        'indexedAutoId' => 'int',
        'vin' => 'string',
    ];

    function __construct(array $data)
    {
        parent::__construct($data, self::map);
    }

    /**
     * @return bool
     */
    public function isIndexed(): bool
    {
        return $this->indexed;
    }

    /**
     * @return int
     */
    public function getIndexedAutoId(): ?int
    {
        return $this->indexedAutoId;
    }

    /**
     * @return string
     */
    public function getVin(): string
    {
        return $this->vin;
    }
}