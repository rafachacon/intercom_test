<?php

namespace App\Service;

use App\Entity\Phone;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class PhoneHelper
{
    private $em;

    /**
     * PhoneHelper constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param string $phone
     * @return bool
     */
    public function exists($phone)
    {
        $phoneRepo = $this->em->getRepository(Phone::class);

        $data = $phoneRepo->findBy(['phone' => $phone]);

        return !empty($data);
    }

    /**
     * @param $phone
     * @return false|int
     */
    public function validate($phone)
    {
        return preg_match("/^(\+34|0034|34)?[ -]*([67])[ -]*([0-9][ -]*){8}/", $phone);
    }

    /**
     * @param string $phone
     * @param string $timestamp
     * @return string
     */
    public function generateCode($phone, $timestamp)
    {
        return strtoupper(
            substr(
                sha1(sha1($timestamp) . sha1($phone)), 0, 4
            )
        );
    }

    /**
     * @param Phone $phone
     * @return bool
     */
    public function expired(Phone $phone)
    {
        $dateAdd = $phone->getDateAdd()->getTimestamp();
        $dateCheck = (new DateTime())->getTimestamp();

        return ($dateCheck - $dateAdd) > (5 * 60);
    }
}