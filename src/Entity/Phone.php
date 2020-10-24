<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Phone
 *
 * @ORM\Table(name="phones")
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks()
 */
class Phone
{

    const STATUS_PENDING = 0;
    const STATUS_VERIFIED = 1;
    const STATUS_REJECTED = 2;
    const STATUS_EXPIRED = 3;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="phone", type="string", length=13)
     */
    private $phone;

    /**
     * @ORM\Column(name="code", type="string", length=4)
     */
    private $code;

    /**
     * @var DateTime $dateAdd
     *
     * @ORM\Column(name="date_add", type="datetime", nullable=false)
     */
    private $dateAdd;

    /**
     * @var DateTime $dateCheck
     *
     * @ORM\Column(name="date_check", type="datetime", nullable=true)
     */
    private $dateCheck;

    /**
     * @var int $status
     *
     * @ORM\Column(name="status", type="integer", nullable=true, options={"default": 0})
     */
    private $status;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return DateTime
     */
    public function getDateAdd(): DateTime
    {
        return $this->dateAdd;
    }

    /**
     * @param DateTime $dateAdd
     */
    public function setDateAdd(DateTime $dateAdd): void
    {
        $this->dateAdd = $dateAdd;
    }

    /**
     * @return DateTime
     */
    public function getDateCheck(): DateTime
    {
        return $this->dateCheck;
    }

    /**
     * @param DateTime $dateCheck
     */
    public function setDateCheck(DateTime $dateCheck): void
    {
        $this->dateCheck = $dateCheck;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }
}