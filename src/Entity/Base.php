<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

abstract class Base
{
    const DATE_FORMAT = "Y-m-d";
    const DATE_TIME_FORMAT = "m-d-Y H:i:s";

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $_created;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $_updated;

    #[ORM\Column(type: "datetime", nullable: true)]
    protected ?\DateTime $_deleted;

    public function getCreated(): ?\DateTime
    {
        return $this->_created;
    }

    public function setCreated($created)
    {
        $this->_created = $created;
        return $this;
    }

    public function getUpdated(): ?\DateTime
    {
        return $this->_updated;
    }

    public function setUpdated($updated)
    {
        $this->_updated = $updated;
        return $this;
    }

    public function getDeleted(): ?\DateTime
    {
        return $this->_deleted;
    }

    public function setDeleted($deleted)
    {
        $this->_deleted = $deleted;
        return $this;
    }

    public function isDelete(): bool {
        return !is_null($this->_deleted);
    }

    public function getStringFromDate(\DateTimeInterface $date = null, $isDateTime = true, $format = null)
    {
        $format = ((!empty($format)) ? $format : $isDateTime) ?
            self::DATE_TIME_FORMAT :
            self::DATE_FORMAT;

        return ($date) ? $date->format($format) : null;
    }
}
