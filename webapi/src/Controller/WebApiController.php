<?php

namespace App\Controller;

use App\DataTransferObject\ConversionData;
use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class WebApiController extends AbstractController
{
    protected Logger $logger;

    public function __construct()
    {
        $this->logger = new Logger('logger');

        $lineFormatter = new LineFormatter('[%datetime%] %message%' . PHP_EOL);

        $streamHandler = new StreamHandler('php://stdout');
        $streamHandler->setFormatter($lineFormatter);
        $this->logger->pushHandler($streamHandler);
    }

    protected static function ensureBearer(Request $request): ?Response
    {
        $bearer = null;

        $authHeader = $request->headers->get('Authorization');

        if ($authHeader) {
            $bearer = preg_match('/^Bearer (.+)$/', $authHeader, $matches) ? $matches[1] : null;
        }

        if ($bearer !== $_ENV['BEARER']) {
            return new Response(null, Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    #[Route('/conversion', methods: ['POST'], format: 'json')]
    public function conversion(
        Request $request,
        #[MapRequestPayload(acceptFormat: 'json', validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        ConversionData $conversionData,
    ): Response
    {
        $date = microtime(true);

        $unauthorized = self::ensureBearer($request);

        if ($unauthorized instanceof Response) {
            return $unauthorized;
        }

        try {
            if ($conversionData->contents !== null) {
                $contents = base64_decode($conversionData->contents);
            } else {
                $httpClient = HttpClient::create();
                $response = $httpClient->request('GET', $conversionData->location);
                $contents = $response->getContent();
            }
        } catch (Exception $e) {
            return new Response('Contents error! ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        $markdown = '';

        try {
            $tempFile = tempnam(sys_get_temp_dir(), 'phpword');
            file_put_contents($tempFile, $contents);
            $phpWord = IOFactory::load($tempFile);
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if ($element instanceof Title) {
                        $level = $element->getDepth();
                        $level++;  // Increase the level of the title by one
                        $text = $element->getText();
                        if ($text instanceof TextRun) {
                            $text = $text->getText();
                        }
                        $markdown .= str_repeat('#', $level);
                        $markdown .= ' ';
                        $markdown .= $text;
                        $markdown .= PHP_EOL;
                        $markdown .= PHP_EOL;
                    }
                    else if ($element instanceof TextRun) {
                        // TODO: Line breaks
                        // TODO: Bold
                        // TODO: Italic
                        $markdown .= $element->getText();
                        $markdown .= PHP_EOL;
                        $markdown .= PHP_EOL;
                    }
                }
            }
            $this->logger->info(sprintf('Document successfully converted in %d milliseconds.', round(microtime(true) - $date,3) * 1000));
        } catch (Exception $e) {
            return new Response('Conversion error! ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        } finally {
            unlink($tempFile);
        }

        return $this->json([
            'markdown' => $markdown,
        ], Response::HTTP_OK);
    }
}

