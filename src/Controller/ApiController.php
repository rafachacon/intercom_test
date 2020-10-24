<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Service\PhoneHelper;
use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

/**
 * Class ApiController
 *
 * @Route("/api")
 */
class ApiController extends AbstractFOSRestController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PhoneHelper $phoneService
     */
    private $phoneHelper;

    /**
     * ApiController constructor.
     * @param SerializerInterface $serializer
     * @param PhoneHelper $phoneHelper
     */
    public function __construct(
        SerializerInterface $serializer,
        PhoneHelper $phoneHelper
    )
    {
        $this->serializer = $serializer;
        $this->phoneHelper = $phoneHelper;
    }

    /**
     * @Rest\Post("/v1/verifications.{_format}", name="phones_get_code", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Gets code and verificationId given a phone number."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to get the code."
     * )
     *
     *
     * @SWG\Parameter(
     *     name="phone",
     *     in="body",
     *     type="string",
     *     description="The phone number",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="verificationId",
     *     in="body",
     *     type="string",
     *     description="The verirication Id",
     *     schema={}
     * )
     *
     * @SWG\Tag(name="Codes")
     * @param Request $request
     * @return Response
     */
    public function getCodeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = [];
        $message = '';

        try {
            $responseCode = Response::HTTP_OK;
            $error = false;

            $phone = $request->get('phone');

            $phoneCollection = $em->getRepository('App:Phone')->findBy(
                [
                    'phone' => $phone,
                ],
                ['id' => 'ASC']
            );

            if (empty($phoneCollection)) {
                $responseCode = Response::HTTP_NOT_FOUND;
                $error = true;
                $message = "No code found.";
            } else {
                $phoneObject = $phoneCollection[0];
                $data = [
                    'verificationId' => $phoneObject->getId(),
                    'code' => $phoneObject->getCode()
                ];
            }
        } catch (Exception $e) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $error = true;
            $message = 'An error has occurred: ' . $e->getMessage();
        }

        $response = [
            'code' => $responseCode,
            'error' => $error,
            'data' => $error ? $message : $data,
        ];

        return new Response(
            $this->serializer->serialize($response, "json"),
            $responseCode,
            [
                'Access-Control-Allow-Origin' => '*'
            ]
        );
    }

    /**
     * @Rest\Post("/v1/verifications/add.{_format}", name="phones_add_phone", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Adds a phone and creates a code for it."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to add the phone."
     * )
     *
     *
     * @SWG\Parameter(
     *     name="phone",
     *     in="body",
     *     type="string",
     *     description="The phone number",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="Codes")
     * @param Request $request
     * @return Response
     */
    public function addPhone(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = [];
        $message = '';

        try {
            $responseCode = Response::HTTP_OK;
            $error = false;

            $phone = $request->get('phone', null);

            $phoneExists = $this->phoneHelper->exists($phone);

            $isValid = $this->phoneHelper->validate($phone);

            if ($phone != null && !$phoneExists && $isValid) {
                $dateAdd = new DateTime();

                $code = $this->phoneHelper->generateCode($phone, $dateAdd->getTimestamp());

                $phoneObject = new Phone();
                $phoneObject->setPhone($phone);
                $phoneObject->setDateAdd($dateAdd);
                $phoneObject->setStatus(Phone::STATUS_PENDING);
                $phoneObject->setCode($code);

                $em->persist($phoneObject);
                $em->flush();

                $data = [
                    'verificationId' => $phoneObject->getId(),
                    'code' => $code
                ];

                // todo: send an SMS to the phone.
                // Here's where an SMS would be sent.

            } else {
                $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $error = true;
                $message = "The phone '{$phone}' is not valid.";
            }

        } catch (Exception $e) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $error = true;
            $message = 'An error has occurred: ' . $e->getMessage();
        }

        $response = [
            'code' => $responseCode,
            'error' => $error,
            'data' => $error ? $message : $data,
        ];

        return new Response(
            $this->serializer->serialize($response, "json"),
            $responseCode,
            [
                'Access-Control-Allow-Origin' => '*'
            ]
        );
    }

    /**
     * @Rest\Post("/v1/verifications/verify.{_format}", name="phones_verify", defaults={"_format":"json"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Verifies a phone given the verification Id and the code."
     * )
     *
     * @SWG\Response(
     *     response=500,
     *     description="An error has occurred trying to verify the code."
     * )
     *
     *
     * @SWG\Parameter(
     *     name="verificationId",
     *     in="body",
     *     type="string",
     *     description="The verification Id generated when the phone was added.",
     *     schema={}
     * )
     *
     * @SWG\Parameter(
     *     name="code",
     *     in="body",
     *     type="string",
     *     description="The code",
     *     schema={}
     * )
     *
     *
     * @SWG\Tag(name="Codes")
     * @param Request $request
     * @return Response
     */
    public function verify(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = [];
        $message = '';

        try {
            $responseCode = Response::HTTP_OK;
            $error = false;

            $verificationId = $request->get('verificationId', null);
            $code = $request->get('code', null);

            $phoneRepo = $em->getRepository('App:Phone');

            $phoneCollection = $phoneRepo->findBy(
                [
                    'id' => $verificationId,
                    'code' => $code
                ]
            );

            // The phone must exist.
            if (!empty($phoneCollection)) {
                /** @var Phone $phoneObject */
                $phoneObject = $phoneCollection[0];

                // The phone must be in status "pending".
                $status = $phoneObject->getStatus();

                if ($status == Phone::STATUS_PENDING) {

                    // Check expiration.
                    $expired = $this->phoneHelper->expired($phoneObject);

                    $phoneObject->setDateCheck(new DateTime());

                    if (!$expired) {
                        $codeBuild = $this->phoneHelper->generateCode($phoneObject->getPhone(), $phoneObject->getDateAdd()->getTimestamp());
                        if ($code === $codeBuild) {
                            $phoneObject->setStatus(Phone::STATUS_VERIFIED);
                        } else {
                            $phoneObject->setStatus(Phone::STATUS_REJECTED);
                        }
                    } else {
                        $phoneObject->setStatus(Phone::STATUS_EXPIRED);
                    }

                    $em->persist($phoneObject);
                    $em->flush();

                    $data = [
                        'phone' => $phoneObject->getPhone(),
                        'status' => $phoneObject->getStatus()
                    ];
                } else {
                    $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                    $error = true;
                    $message = 'No such a phone.';
                }
            } else {
                $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
                $error = true;
                $message = 'The code is not valid.';
            }

        } catch (Exception $e) {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $error = true;
            $message = 'An error has occurred: ' . $e->getMessage();
        }

        $response = [
            'code' => $responseCode,
            'error' => $error,
            'data' => $error ? $message : $data,
        ];

        return new Response(
            $this->serializer->serialize($response, "json"),
            $responseCode,
            [
                'Access-Control-Allow-Origin' => '*'
            ]
        );
    }

}
